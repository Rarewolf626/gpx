<?php

namespace GPX\Exception;

use Throwable;

class ExceptionHandler implements \Illuminate\Contracts\Debug\ExceptionHandler {
    /**
     * Report or log an exception.
     *
     * @param  Throwable $e
     *
     * @return void
     */
    public function report(Throwable $e)
    {
        gpx_logger()->error('Queued Job failure: ' . $e->getMessage(), ['exception' => $e]);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Throwable $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e)
    {
        // TODO: Implement render() method.
    }

    /**
     * Render an exception to the console.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @param  \Throwable $e
     *
     * @return void
     */
    public function renderForConsole($output, Throwable $e)
    {
        $output->writeln('<error>' . $e->getMessage() . '</error>');
        $output->writeln('<error>' . $e->getTraceAsString() . '</error>');
    }

    public function shouldReport( Throwable $e ) {
        return false;
    }
}
