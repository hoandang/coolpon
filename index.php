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

// GET CATEGORIES
$app->get('/categories', function() use ($app) {
    try
    {
        $categories = R::findAll('categories');
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($categories));
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});

$app->get('/categories/search', function() use ($app) {
    $q = $app->request()->get('q');
    try
    {
        $categories = R::find('categories', 'name like ?', array("%%%$q%%"));
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($categories));
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});

// GET CATEGORIES FROM MACHINE
$app->get('/machines/:id/categories', function($id) use ($app) {
    try 
    {
        $sql = "SELECT DISTINCT c.id, c.name ".
            "FROM services s JOIN machines m, categories c ".
            "WHERE m.id = $id ".
            "AND c.id = s.category_id AND m.id = s.machine_id";
        $records = R::getAll($sql);
        $categories = R::convertToBeans('categories', $records);
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($categories));
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});

// GET CATEGORIES/:ID
$app->get('/categories/:id', function($id) use ($app) {
    try 
    {
        $category = r::findOne('categories', 'id=?', array($id));
        $app->response()->header('content-type', 'application/json');
        echo json_encode(r::exportall($category));
    } 
    catch (exception $e)
    {
        response_json_error($app, 400, $e->getmessage());
    }
});

// POST CATEGORY
$app->post('/categories', function() use ($app) {
    try
    {
        $request = $app->request();
        $body    = $request->getBody();
        $input   = json_decode($body);

        $name = $input->name;

        if ($name == '')
            response_json_error($app, 400, 'Bad Request');
        else
        {
            $categories = R::dispense('categories');
            $categories->name = $name;
            $id = R::store($categories);
            $app->response()->header('Content-Type', 'application/json');
            echo json_encode(R::exportAll($categories));
        }
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});
// PUT CATEGORY
$app->put('/categories/:id', function($id) use ($app) {
    try
    {
        $request = $app->request();
        $body    = $request->getBody();
        $input   = json_decode($body);

        $name     = $input->name;

        if ($name == '')
            response_json_error($app, 400, 'Bad Request');
        else
        {
            $category = R::findOne('categories', 'id=?', array($id));
            $category->name = (string)$name;

            $id = R::store($category);

            $app->response()->header('Content-Type', 'application/json');
            echo json_encode(R::exportAll($category));
        }
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});
// DELETE CATEGORY
$app->delete('/categories/:id', function($id) use ($app) {
    try
    {
        $category = R::findOne('categories', 'id=?', array($id));
        if ($category)
        {
            R::trash($category);
            $app->response()->status(204);
        }
        else
            response_json_error($app, 404, 'Resource Not Found');
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});

// GET LOCATIONS
$app->get('/locations/search', function() use ($app) {
    if (strlen($app->request()->get('q')) >= 3)
    {
        $query    = explode(' ', $app->request()->get('q'));
        $postcode = '-1'; 
        $location = '-1'; 
        $locations;

        if (sizeof($query) <= 1) 
        {
            if (is_numeric($query[0]))
                $postcode = $query[0];
            else
                $location = $query[0];
            $locations = R::find('australia_postcode', 'postcode like ? or location like ?', array("%$postcode%", "%$location%"));
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

// GET MACHINES
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
// GET MACHINE/:ID
$app->get('/machines/:id', function($id) use ($app) {
    try 
    {
        $machine = r::findOne('machines', 'id=?', array($id));
        $app->response()->header('content-type', 'application/json');
        echo json_encode(r::exportall($machine));
    } 
    catch (exception $e)
    {
        response_json_error($app, 501, $e->getmessage());
    }
});

// GET COUPONS BY MACHINE
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

// POST MACHINES
$app->post('/machines', function() use ($app) {
    try
    {
        $request = $app->request();
        $body    = $request->getBody();
        $input   = json_decode($body);

        $name       = $input->name;
        $suburb     = $input->suburb;
        $address    = $input->address;

        if ($name == '' || $suburb == '' || $address == '')
            response_json_error($app, 400, 'Bad Request');
        else
        {
            $machine = R::dispense('machines');

            $machine->name    = (string)$name;
            $machine->suburb  = (string)strtoupper($suburb);
            $machine->address = (string)strtoupper($address);

            $machine_id = R::store($machine);

            if ($input->categories != '') {
                $categories = explode(',', $input->categories);
                try
                {
                    foreach($categories as $category_id) 
                    {
                        $service = R::dispense('services');
                        $service->machine_id  = $machine_id;
                        $service->category_id = $category_id;
                        $service_id = R::store($service);
                    }
                }
                catch (Exception $e) 
                {
                    response_json_error($app, 400, $e->getMessage());
                }
            }

            $app->response()->header('Content-Type', 'application/json');
            echo json_encode(R::exportAll($machine));
        }
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});

// PUT MACHINES
$app->put('/machines/:id', function($id) use ($app) {
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
            $machine = R::findOne('machines', 'id=?', array($id));
            $machine->name    = (string)$name;
            $machine->suburb  = (string)strtoupper($suburb);
            $machine->address = (string)strtoupper($address);

            $machine_id = R::store($machine);

            R::exec("DELETE FROM services WHERE machine_id = $machine_id");

            if ($input->categories != '') {
                $categories = explode(',', $input->categories);
                try
                {
                    foreach($categories as $category_id) 
                    {
                        $service = R::dispense('services');
                        $service->machine_id  = $machine_id;
                        $service->category_id = $category_id;
                        $service_id = R::store($service);
                    }
                }
                catch (Exception $e) 
                {
                    response_json_error($app, 400, $e->getMessage());
                }
            }

            $app->response()->header('Content-Type', 'application/json');
            echo json_encode(R::exportAll($machine));
        }
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});

// DELETE MACHINES
$app->delete('/machines/:id', function($id) use ($app) {
    try
    {
        $machine = R::findOne('machines', 'id=?', array($id));
        if ($machine)
        {
            R::trash($machine);
            $app->response()->status(204);
        }
        else
            response_json_error($app, 404, 'Resource Not Found');
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});

// GET COUPONS
$app->get('/coupons', function() use ($app) {
    try 
    {
        $sql= 'SELECT c.*, '.
              'm.name "machine_name", m.suburb "machine_suburb", m.address "machine_address", '.
              'b.name "business_name", b.address "business_address", b.description "business_desc "'.
              'FROM coupons c '.
              'JOIN machines m ON c.machine_id = m.id '.
              'JOIN businesses b ON c.business_id = b.id';
        $records = R::getAll($sql);
        $coupons = R::convertToBeans('coupons', $records);
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($coupons));
    } 
    catch (Exception $e)
    {
        response_json_error($app, 404, $e->getMessage());
    }
});

// GET COUPON
$app->get('/coupons/:id', function($id) use ($app) {
    try 
    {
        $sql= 'SELECT c.*, '.
              'm.name "machine_name", m.suburb "machine_suburb", m.address "machine_address", '.
              'b.name "business_name", b.address "business_address", b.description "business_desc "'.
              'FROM coupons c '.
              'JOIN machines m ON c.machine_id = m.id '.
              'JOIN businesses b ON c.business_id = b.id '.
              'WHERE c.id = '.$id;
        $record = R::getAll($sql);
        $coupon = R::convertToBeans('coupon', $record);
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($coupon));
    } 
    catch (Exception $e)
    {
        response_json_error($app, 404, $e->getMessage());
    }
});

// POST COUPON
$app->post('/coupons', function() use ($app) {
    $request = $_POST['request'];

    $machine_id   = $_POST['machine_id'];
    $business_id  = $_POST['business_id'];
    $expired_date = $_POST['expired_date'];
    $name         = $_POST['name'];
    $description  = $_POST['description'];

    if ($machine_id == '' || $business_id == '' || $expired_date == ''
        || $name == '') 
    {
        echo '<a href="javascript:history.back()">Back</a>';
        echo '<br/>';
        echo 'Bad Request.<br/>';
        echo 'Mandatory fields need to be filled.';
    }
    else
    {
        try
        {
            $file_path = '';
            if (isset($_POST['id']))
            {
                $coupon_id = $_POST['id'];
                $coupon = R::findOne('coupons', 'id=?', array($coupon_id));
                $coupon->machine_id   = (int)$machine_id;
                $coupon->expired_date = (string)$expired_date;
                $coupon->business_id  = (int)$business_id;
                $coupon->name         = (string)$name;
                $coupon->description  = (string)$description;
                try
                {
                    $storage = new \Upload\Storage\FileSystem('assets');
                    $file = new \Upload\File('image', $storage);

                    $new_filename = uniqid();
                    $file->setName($new_filename);

                    $file->upload();
                    $file_path = 'assets/'.$file->getNameWithExtension();
                }
                catch (Exception $e) {
                    $file_path = $coupon->image;
                }
                $coupon->image = $file_path;
                $id = R::store($coupon);
            }
            else
            {
                try
                {
                    $storage = new \Upload\Storage\FileSystem('assets');
                    $file = new \Upload\File('image', $storage);

                    $new_filename = uniqid();
                    $file->setName($new_filename);

                    $file->upload();
                    $file_path = 'assets/'.$file->getNameWithExtension();
                }
                catch (Exception $e)
                {
                    $file_path = 'assets/no-image.jpg';
                }
                $coupon = R::dispense('coupons');
                $coupon->machine_id   = (int)$machine_id;
                $coupon->expired_date = (string)$expired_date;
                $coupon->business_id  = (int)$business_id;
                $coupon->name         = (string)$name;
                $coupon->description  = (string)$description;
                $coupon->image        = $file_path;
                $id = R::store($coupon);
            }

            if ($request == 'coupon')
                header('Location: /admin#/coupons');
            else
                header('Location: /admin#/machines/'.$_POST['machine_id']);
            exit();
        }
        catch (Exception $e) {
            echo $e->getMessage().' at line '.$e->getLine();
        }
    }
});

// DELETE COUPON
$app->delete('/coupons/:id', function($id) use ($app) {
    try
    {
        $coupon = R::findOne('coupons', 'id=?', array($id));
        if ($coupon)
        {
            R::trash($coupon);
            $app->response()->status(204);
        }
        else
            response_json_error($app, 404, 'Resource Not Found');
    }
    catch (Exception $e) {
        response_json_error($app, 400, $e->getMessage());
    }
});

// GET BUSINESSES
$app->get('/businesses', function() use ($app) {
    try 
    {
        $businesses = R::findAll('businesses');
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($businesses));
    } 
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});

// GET BUSINESS
$app->get('/businesses/:id', function($id) use ($app) {
    try 
    {
        $business = R::findOne('businesses', 'id=?', array($id));
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($business));
    } 
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});

// POST BUSINESS
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
        response_json_error($app, 400, $e->getMessage());
    }
});
// PUT BUSINESSES
$app->put('/businesses/:id', function($id) use ($app) {
    try
    {
        $request = $app->request();
        $body    = $request->getBody();
        $input   = json_decode($body);

        $name        = $input->name;
        $address     = $input->address;
        $description = $input->description;

        if ($name == '' || $address == '')
            response_json_error($app, 400, 'Bad Request');
        else
        {
            $business = R::findOne('businesses', 'id=?', array($id));
            $business->name = (string)$name;
            $business->address = (string)$address;
            $business->description = (string)$description;

            $id = R::store($business);

            $app->response()->header('Content-Type', 'application/json');
            echo json_encode(R::exportAll($business));
        }
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});
// DELETE BUSINESS
$app->delete('/businesses/:id', function($id) use ($app) {
    try
    {
        $business = R::findOne('businesses', 'id=?', array($id));
        if ($business) 
        {
            R::trash($business);
            $app->response()->status(204);
        }
        else
            response_json_error($app, 404, 'Resource Not Found');
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
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
