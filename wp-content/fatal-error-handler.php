<?php

use Illuminate\Support\Arr;

class GPX_Fatal_Error_Handler extends WP_Fatal_Error_Handler {
    public function handle() {
        try {
            $error = $this->detect_error();
            if ($error) {
                $this->logError($error);
                $this->ray($error);
            }
        } catch (Exception $e) {

        }
        parent::handle();
    }

    public function logError(?array $error = null) {
        if (!$error) return;
        gpx_logger()->error($error['message'], Arr::except($error, 'message'));
    }

    public function ray(?array $error = null) {
        if (function_exists('ray')) {
            ray($error);
            ray()->trace();
        }
    }
}

return new GPX_Fatal_Error_Handler;
