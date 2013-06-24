<?php
require 'vendor/autoload.php';
require 'redbean/rb.php';

// Connect to mysql via redbean
R::setup('mysql:host=localhost; dbname=coolpon', 'root','');
R::freeze(true);

// Initialise Slim
$app = new \Slim\Slim();

// GET home page from template
$app->get('/', function() use ($app) {
    $app->render('public/app.html');
});

// GET admin page from template
$app->get('/admin', function() use ($app) {
    $app->render('admin/admin.html');
});

// GET requests for /machines and /machines?post_code=?
$app->get('/machines', function() use ($app) {
    try 
    {
        $post_code = $app->request()->get('post_code');
        if ($post_code)
        {
            $machines = R::find('machines', 'post_code=?', array($post_code));
            $app->response()->header('Content-Type', 'application/json');
            echo json_encode(R::exportAll($machines));
        }
        else
        {
            $machines = R::find('machines');
            $app->response()->header('Content-Type', 'application/json');
            echo json_encode(R::exportAll($machines));
        }
    } 
    catch (Exception $e)
    {
        $app->response()->status(404);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});

// GET requests for /machines/:id
$app->get('/machines/:id', function($id) use ($app) {
    try 
    {
        $machine = R::find('machines', 'id=?', array($id));
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($machine));
    } 
    catch (Exception $e)
    {
        $app->response()->status(404);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});

// GET requests for /machines/:id/coupons
$app->get('/machines/:id/coupons', function($id) use ($app) {
    try 
    {
        $coupons = R::find('coupons', 'machine_id=?', array($id));
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($coupons));
    } 
    catch (Exception $e)
    {
        $app->response()->status(404);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});

// POST requests to /machines
$app->post('/machines', function() use ($app) {
    try
    {
        $request = $app->request();
        $body    = $request->getBody();
        $input   = json_decode($body);

        $machine = R::dispense('machines');

        $machine->name      = (string)$input->name;
        $machine->post_code = (int)$input->post_code;
        $machine->address   = (string)$input->address;

        $id = R::store($machine);

        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($machine));
    }
    catch (Exception $e)
    {
        $app->response()->status(404);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});

// POST requests to /machines/:id/coupons
$app->post('/machines/:id/coupons', function($id) use ($app) {
    try
    {
        $request = $app->request();
        $body    = $request->getBody();
        $input   = json_decode($body);

        $coupon = R::dispense('coupons');

        $coupon->machine_id  = (int)$input->machine_id;
        $coupon->name        = (string)$input->name;
        $coupon->description = (string)$input->description;
        $coupon->image       = (string)$input->image;

        $id = R::store($coupon);

        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($coupon));
    }
    catch (Exception $e)
    {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});

// Run awesome app
$app->run();
