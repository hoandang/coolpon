<?php
require 'vendor/autoload.php';
require 'redbean/rb.php';

// Connect to mysql via redbean
R::setup('mysql:host=localhost; dbname=coolpon', 'root','');
R::freeze(true);

// Initialise Slim
$app = new \Slim\Slim();

$app->config(array(
    'templates.path' => 'public'
));

// GET home page from template
$app->get('/', function() use ($app) {
    $app->render('app/app.html');
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

// GET requests for /businesses
$app->get('/businesses', function() use ($app) {
    try 
    {
        $businesses = R::find('businesses');
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($businesses));
    } 
    catch (Exception $e)
    {
        $app->response()->status(404);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});

// POST requests to /businesses
$app->post('/businesses', function() use ($app) {
    try
    {
        $request = $app->request();
        $body    = $request->getBody();
        $input   = json_decode($body);

        $businesses = R::dispense('businesses');

        $businesses->name        = (string)$input->name;
        $businesses->address     = (string)$input->address;
        $businesses->description = (string)$input->description;

        $id = R::store($businesses);

        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($businesses));
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
        $storage = new \Upload\Storage\FileSystem('assets');
        $file = new \Upload\File('image', $storage);
 
        try 
        {
            $file->upload();
        } 
        catch (Exception $e) 
        {
            $app->response()->status(400);
            $app->response()->header('X-Status-Reason', $file->getErrors());
        }

        $file_path = 'assets/'.$file->getNameWithExtension();
        $coupon = R::dispense('coupons');

        $coupon->machine_id  = $_POST['machine_id'];
        $coupon->business_id = 1;
        $coupon->name        = $_POST['name'];
        $coupon->description = $_POST['description'];
        $coupon->image       = $file_path;


        $id = R::store($coupon);
        header('Location: /admin#/machines/'.$_POST['machine_id']); 
        exit();
    }
    catch (Exception $e)
    {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});

// Run awesome app
$app->run();
