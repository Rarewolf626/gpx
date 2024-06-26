<?php

namespace GPX\GPXAdmin\Router;

use Spatie\Url\Url;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use GPX\Exception\NoMatchingRouteException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GpxAdminRouter {

    protected Collection $routes;

    public function __construct(protected Container $container) {
        $this->routes = new Collection();
    }

    public function addPage(string $page, $callback, array $params = []): self {
        $this->routes->add(AdminRoute::create($page, $callback, $params, false));

        return $this;
    }

    public function addApi(string $page, $callback, array $params = []): self {
        $this->routes->add(AdminRoute::create($page, $callback, $params, true));

        return $this;
    }

    public function url(string $page = '', array $params = []): string {
        $url = Url::fromString(admin_url('admin.php'));
        if (in_array($page, ['', 'dashboard'])) {
            return $url->withQueryParameters(['page' => 'gpx-admin-page'])->__toString();
        }

        return $url->withQueryParameters(array_merge($params, [
            'page' => 'gpx-admin-page',
            'gpx-pg' => $page,
        ]))->__toString();
    }

    public function dispatch(Request $request = null): void {
        $request = $request ?? gpx_request();
        $route = $this->matchPage($request);
        if (!$route) {
            throw (new NoMatchingRouteException())->setRequest($request);
        }
        $args = Arr::only($request->input(), $route->params);
        $this->dispatchRoute($route, $args);
    }

    public function dispatchRoute(AdminRoute $route, array $params = []): void {
        $args = Arr::only($params, $route->params);
        $callable = $route->callable;
        if (is_string($callable) && Str::match('/[^@]+@[^@]+/i', $callable)) {
            $callable = explode('@', $callable, 1);
        }
        if (is_array($callable) && count($callable) === 2) {
            $class = $this->container->make($callable[0]);
            $callable = [$class, $callable[1]];
        }
        try {
            $response = $this->container->call($callable, $args);
        } catch (ModelNotFoundException $e) {
            $request = gpx_request();
            if ($request->ajax()) {
                wp_send_json(['success' => false, 'message' => 'Page Not Found'], 404);

                return;
            }
            $dashboard = admin_url('admin.php?page=gpx-admin-page');
            $active = 'dashboard';
            $title = 'Page Not Found';
            $message = '';
            global $wp_query;
            status_header(404);
            $wp_query->set_404();
            gpx_admin_view('404', compact('title', 'message', 'dashboard', 'active'), true);

            return;
        }
        if ($response instanceof Response) {
            gpx_send_response($response, false);

            return;
        }
        if ($response instanceof Renderable) {
            $response = $response->render();
        }
        if (is_string($response) || is_numeric($response)) {
            status_header(200);
            echo $response;

            return;
        }
        if (is_array($response)) {
            wp_send_json($response, 200);
        }
    }

    public function matchPage(Request $request): ?AdminRoute {
        if ($request->query->get('page') !== 'gpx-admin-page') {
            return null;
        }

        return $this->routes->first(function (AdminRoute $route) use ($request) {
            return $route->isPage($request->query->get('gpx-pg', '')) && !$route->isApi();
        });
    }

    public function matchApi(string $path): ?AdminRoute {
        return $this->routes->first(function (AdminRoute $route) use ($path) {
            return $route->isPage($path) && $route->isApi();
        });
    }

    public function dispatchApi(string $path): void {
        global $wp_query;
        $route = $this->matchApi($path);
        if (!$route) {
            throw (new NoMatchingRouteException())->setRequest(gpx_request());
            $wp_query->set_404();
            status_header(404);

            return;
        }
        if ($wp_query->is_404) {
            $wp_query->is_404 = false;
        }
        $request = gpx_request();
        $params = Arr::only($request->input(), $route->params);
        $this->dispatchRoute($route, $params);
    }
}
