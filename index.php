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
    $app->render('admin/index.html');
});

// GET requests for /locations
$app->get('/locations/search', function() use ($app) {
    if (strlen($app->request()->get('q')) >= 3)
    {
        $query     = explode(' ', $app->request()->get('q'));
        $postcode = '-1'; 
        $location = '-1'; 
        $locations;

        if (sizeof($query) <= 1) 
        {
            if (is_numeric($query[0]))
                $postcode = $query[0];
            else
                $location = $query[0];
            $locations = R::find('australia_postcode', 'postcode like ? or location like ?', 
                                array("%$postcode%", "%$location%"));
        }
        else
        {
            $postcode  = is_numeric($query[0]) ? $query[0] : $query[1];
            $location  = is_numeric($query[0]) ? $query[1] : $query[0];
            $locations = R::find('australia_postcode', 'postcode like ? and location like ?', 
                                array("%$postcode%", "%$location%"));
        }
        if (sizeof($locations) > 0 )
        {
            $app->response()->header('Content-Type', 'application/json');
            echo json_encode(R::exportAll($locations));
        }
        else
            response_json_error($app, 404, 'Invalid location and post code');
    }
    else
        response_json_error($app, 411, 'Ihe query is required at least 3 characters');
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
            $machines = R::findAll('machines');
            $app->response()->header('Content-Type', 'application/json');
            echo json_encode(R::exportAll($machines));
        }
    } 
    catch (Exception $e)
    {
        response_json_error($app, 501, $e->getMessage());
    }
});

// GET requests for /machines/:id
$app->get('/machines/:id', function($id) use ($app) {
    try 
    {
        $machine = R::findOne('machines', 'id=?', array($id));
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($machine));
    } 
    catch (Exception $e)
    {
        response_json_error($app, 501, $e->getMessage());
    }
});

// GET requests for /machines/:id/coupons
$app->get('/machines/:id/coupons', function($id) use ($app) {
    try 
    {
        //$coupons = R::find('coupons', ' machine_id=? ORDER BY id DESC ', array($id));
        $coupons = R::find('coupons', 'machine_id=?', array($id));
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($coupons));
    } 
    catch (Exception $e)
    {
        response_json_error($app, 501, $e->getMessage());
    }
});

// GET requests for /coupons
$app->get('/coupons', function() use ($app) {
    try 
    {
        $coupons = R::findAll('coupons');
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($coupons));
    } 
    catch (Exception $e)
    {
        $app->response()->status(404);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});

// GET requests for /coupons/:id
$app->get('/coupons/:id', function($id) use ($app) {
    try 
    {
        $machine = R::findOne('coupons', 'id=?', array($id));
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($machine));
    } 
    catch (Exception $e)
    {
        response_json_error($app, 501, $e->getMessage());
    }
});

// POST requests to /coupons
$app->post('/coupons', function() use ($app) {
    $request = $_POST['request'];
    try
    {
        $storage = new \Upload\Storage\FileSystem('assets');
        $file = new \Upload\File('image', $storage);

        $new_filename = uniqid();
        $file->setName($new_filename);

        try 
        {
            $file->upload();
            $file_path = 'assets/'.$file->getNameWithExtension();

            $machine_id   = $_POST['machine_id'];
            $business_id  = $_POST['business_id'];
            $expired_date = $_POST['expired_date'];
            $name         = $_POST['name'];
            $description  = $_POST['description'];

            if ($machine_id == '' || $business_id == '' || $expired_date == ''
                || $name == '' || $description == '')
            {
                echo 'Bad Request';
            }
            else
            {
                $coupon = R::dispense('coupons');

                $coupon->machine_id   = (int)machine_id;
                $coupon->expired_date = (string)expired_date;
                $coupon->business_id  = (int)business_id;
                $coupon->name         = (string)name;
                $coupon->description  = (string)description;
                $coupon->image        = $file_path;

                $id = R::store($coupon);
                if ($request == 'coupon')
                    header('Location: /admin#/coupons');
                else
                    header('Location: /admin#/machines/'.$_POST['machine_id']);
                exit();
            }
        } 
        catch (Exception $e) 
        {
            echo $e->getMessage().'<br/>';
            echo $file->getErrors();
        }
    }
    catch (Exception $e)
    {
        echo $e->getMessage();
    }
});

// GET requests for /businesses
$app->get('/businesses', function() use ($app) {
    try 
    {
        $businesses = R::findAll('businesses');
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($businesses));
    } 
    catch (Exception $e)
    {
        response_json_error($app, 501, $e->getMessage());
    }
});

// POST requests to /businesses
$app->post('/businesses', function() use ($app) {
    try
    {
        $request = $app->request();
        $body    = $request->getBody();
        $input   = json_decode($body);

        $name        = $input->name;
        $description = $input->description;
        $address     = $input->address;

        if ($name == '' || $description == '' || $address == '')
            response_json_error($app, 400, 'Bad Request');
        else
        {
            $businesses = R::dispense('businesses');

            $businesses->name        = $name;
            $businesses->address     = $address;
            $businesses->description = $description;

            $id = R::store($businesses);

            $app->response()->header('Content-Type', 'application/json');
            echo json_encode(R::exportAll($businesses));
        }
    }
    catch (Exception $e)
    {
        response_json_error($app, 501, $e->getMessage());
    }
});

// POST requests to /machines
$app->post('/machines', function() use ($app) {
    try
    {
        $request = $app->request();
        $body    = $request->getBody();
        $input   = json_decode($body);

        $name     = $input->name;
        $suburb = $input->suburb;
        $address  = $input->address;

        if ($name == '' || $suburb == '' || $address == '')
            response_json_error($app, 400, 'Bad Request');
        else
        {
            $machine = R::dispense('machines');

            $machine->name    = (string)$name;
            $machine->suburb  = (string)strtoupper($suburb);
            $machine->address = (string)strtoupper($address);

            $id = R::store($machine);

            $app->response()->header('Content-Type', 'application/json');
            echo json_encode(R::exportAll($machine));
        }
    }
    catch (Exception $e)
    {
        response_json_error($app, 501, $e->getMessage());
    }
});

// Run awesome app
$app->run();

function response_json_error($app, $http_code, $msg)
{
    $app->response()->status($http_code);
    $app->response()->header('Content-Type', 'application/json');
    $app->response()->header('X-Status-Reason', $msg);
    echo json_encode(array('error' => $msg));
}
