<?php
namespace App\Controllers;
use App\Controllers\BaseController;

class HealthcheckController extends BaseController
{
    public function health(): void
    {
        $this->json([
            'status' => 'OK',
            'timestamp' => date('c')
        ]);
    }
}
