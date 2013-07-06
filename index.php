<?php
session_start();

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
    $app->render('app/index.html');
});

// GET admin page from template
$app->get('/admin', function() use ($app) {
    if (authorise())
        $app->render('admin/index.html');
    else
    {
        header('Location: /login');
        exit();
    }
});

// GET LOGIN PAGE
$app->get('/login', function() use ($app) {
    if (!authorise())
        $app->render('admin/login.html');
    else
    {
        header('Location: /admin');
        exit();
    }
});

// LOGOUT
$app->get('/logout', function() use ($app) {
    session_destroy();
    header('Location: /');
    exit();
});

function authorise() 
{
    if (!empty($_SESSION['admin']))
        return true;
    else
        return false;
}

// LOGIN
$app->post('/login', function() use ($app) {
    if($_POST['username'] == 'admin' && $_POST['password'] == 'admin') 
    {
        $_SESSION['admin'] = $_POST['username'];
        echo 1;
    }
    else
        echo 0;
});

// GET CATEGORIES
$app->get('/categories', function() use ($app) {
    try
    {
        $uncouponed_categories = R::findAll('categories');
        $categories = array();
        foreach ($uncouponed_categories as $uncouponed_category)
        {
            $sql = 'SELECT co.id, co.name, co.expired_date, co.description '.
                   'FROM categories cat JOIN coupons co ON cat.id = co.category_id '.
                   'WHERE cat.id = '.$uncouponed_category->id;
            $records = R::getAll($sql);
            $coupons = R::convertToBeans('coupons', $records);
            $category = R::dispense('category');
            $category->id = $uncouponed_category->id;
            $category->name = $uncouponed_category->name;
            $category->coupons = $coupons;

            array_push($categories, $category);
        }
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($categories));
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});

// SEARCH CATEGORIES
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
// SEARCH BUSINESSES
$app->get('/businesses/search', function() use ($app) {
    $q = $app->request()->get('q');
    try
    {
        $businesses = R::find('businesses', 'name like ?', array("%%%$q%%"));
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($businesses));
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});
// SEARCH MACHINES
$app->get('/machines/search', function() use ($app) {
    $q = $app->request()->get('m');
    try
    {
        $machines = R::find('machines', 'name like ?', array("%%%$q%%"));
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($machines));
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});

// GET CATEGORIES/:ID
$app->get('/categories/:id', function($id) use ($app) {
    // category
    try 
    {
        $uncouponed_category = R::findOne('categories', 'id=?', array($id));
        $sql = 'SELECT co.id, co.name, co.expired_date, co.description, co.image '.
               'FROM categories cat JOIN coupons co ON co.category_id = cat.id '.
               'WHERE cat.id = '.$id;
        $records = R::getAll($sql);
        $coupons = R::convertToBeans('coupons', $records);
        $category = R::dispense('category');
        $category->id = $id;
        $category->name = $uncouponed_category->name;
        $category->coupons = $coupons;
        $app->response()->header('content-type', 'application/json');
        echo json_encode(R::exportall($category));
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
        $name    = $input->name;

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
        else response_json_error($app, 404, 'Invalid location and post code');
    }
    else response_json_error($app, 411, 'Ihe query is required at least 3 characters');
});

// GET MACHINE LOCATION
$app->get('/machines/search', function() use ($app) {
    $query = $app->request()->get('q');
    if (strlen($query) >= 3)
    {
        $uncouponed_machines = R::find('machines', 'suburb REGEXP ?', array($query));
        $machines = array();
        if (sizeof($uncouponed_machines) > 0)
        {
            foreach ($uncouponed_machines as $uncouponed_machine)
            {
                $sql = 'SELECT c.id, c.name, c.expired_date, c.description, c.image '.
                       'FROM machines m JOIN coupons c ON c.machine_id = m.id '.
                       'WHERE m.id = '.$uncouponed_machine->id;

                $records = R::getAll($sql);
                $coupons = R::convertToBeans('coupons', $records);

                $machine = R::dispense('machine');

                $machine->id      = $uncouponed_machine->id;
                $machine->name    = $uncouponed_machine->name;
                $machine->suburb  = $uncouponed_machine->suburb;
                $machine->address = $uncouponed_machine->address;
                $machine->coupons = $coupons;

                array_push($machines, $machine);
            }
            $app->response()->header('Content-Type', 'application/json');
            echo json_encode(R::exportAll($machines));
        }
        else 
            response_json_error($app, 404, 'Machine Not Found');
    }
    else 
        response_json_error($app, 411, 'The query is required at least 3 characters');
});

// GET MACHINES
$app->get('/machines', function() use ($app) {
    try 
    {
        $uncouponed_machines = R::findAll('machines');
        $machines = array();
        foreach ($uncouponed_machines as $uncouponed_machine)
        {
            $sql = 'SELECT c.id, c.name, c.expired_date, c.description, c.image '.
                    'FROM machines m JOIN coupons c ON c.machine_id = m.id '.
                    'WHERE m.id = '.$uncouponed_machine->id;

            $records = R::getAll($sql);
            $coupons = R::convertToBeans('coupons', $records);

            $machine = R::dispense('machine');

            $machine->id      = $uncouponed_machine->id;
            $machine->name    = $uncouponed_machine->name;
            $machine->suburb  = $uncouponed_machine->suburb;
            $machine->address = $uncouponed_machine->address;
            $machine->coupons = $coupons;

            array_push($machines, $machine);
        }
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($machines));
    } 
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});

// GET MACHINE
$app->get('/machines/:id', function($id) use ($app) {
    try 
    {
        $uncouponed_machine = R::findOne('machines', 'id=?', array($id));

        $sql = 'SELECT c.id, c.name, c.expired_date, c.description, c.image '.
                'FROM machines m JOIN coupons c ON c.machine_id = m.id '.
                'WHERE m.id = '.$id;

        $records = R::getAll($sql);
        $coupons = R::convertToBeans('coupons', $records);

        $machine = R::dispense('machine');

        $machine->id      = $id;
        $machine->name    = $uncouponed_machine->name;
        $machine->suburb  = $uncouponed_machine->suburb;
        $machine->address = $uncouponed_machine->address;
        $machine->coupons = $coupons;

        $app->response()->header('content-type', 'application/json');
        echo json_encode(R::exportall($machine));
    } 
    catch (exception $e)
    {
        response_json_error($app, 400, $e->getmessage());
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

            $id = R::store($machine);

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

            $id = R::store($machine);

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
        $sql= 'SELECT c.id, c.name, c.expired_date, c.image, '.
              'm.id "machine_id", m.name "machine_name", m.suburb "machine_suburb", m.address "machine_address", '.
              'cat.id "category_id", cat.name "category_name", '.
              'b.id "business_id", b.name "business_name", b.address "business_address", b.phone "business_phone", b.email "business_email", b.description "business_desc" '.
              'FROM coupons c '.
              'JOIN machines m ON c.machine_id = m.id '.
              'JOIN categories cat ON c.category_id = cat.id '.
              'JOIN businesses b ON c.business_id = b.id ORDER BY c.id';
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
              'm.id "machine_id", m.name "machine_name", m.suburb "machine_suburb", m.address "machine_address", '.
              'cat.id "category_id", cat.name "category_name", '.
              'b.id "business_id", b.name "business_name", b.address "business_address", b.phone "business_phone", b.email "business_email", b.description "business_desc" '.
              'FROM coupons c '.
              'JOIN machines m ON c.machine_id = m.id '.
              'JOIN categories cat ON c.category_id = cat.id '.
              'JOIN businesses b ON c.business_id = b.id '.
              'WHERE c.id = '. $id;
        $records = R::getAll($sql);
        $coupon = R::convertToBeans('coupon', $records);
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
    $machine_id   = $_POST['machine_id'];
    $category_id  = $_POST['category_id'];
    $business_id  = $_POST['business_id'];
    $expired_date = $_POST['expired_date'];
    $name         = $_POST['name'];
    $description  = $_POST['description'];

    if ($machine_id == '' || $business_id == '' || $expired_date == ''
        || $name == '' || $description == '') 
        echo 'Failed';
    else
    {
        try
        {
            $file_path = '';
            if (isset($_POST['id']))
            {
                $coupon_id = $_POST['id'];
                $coupon = R::findOne('coupons', 'id=?', array($coupon_id));
                $coupon->name         = (string)$name;
                $coupon->machine_id   = (int)$machine_id;
                $coupon->category_id  = (int)$category_id;
                $coupon->business_id  = (int)$business_id;
                $coupon->expired_date = (string)$expired_date;
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
                echo 'Updated';
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
                $coupon->business_id  = (int)$business_id;
                $coupon->category_id  = (int)$category_id;
                $coupon->expired_date = (string)$expired_date;
                $coupon->name         = (string)$name;
                $coupon->description  = (string)$description;
                $coupon->image        = $file_path;
                $id = R::store($coupon);
                echo 'Created';
            }
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
        $uncouponed_businesses = R::findAll('businesses');
        $businesses = array();
        foreach ($uncouponed_businesses as $uncouponed_business)
        {
            $sql = 'SELECT c.id, c.name, c.expired_date, c.description '.
                   'FROM businesses b JOIN coupons c ON c.business_id = b.id WHERE b.id = '.$uncouponed_business->id;
            $records = R::getAll($sql);
            $coupons = R::convertToBeans('coupons', $records);

            $business = R::dispense('business');
            $business->id          = $uncouponed_business->id;
            $business->name        = $uncouponed_business->name;
            $business->address     = $uncouponed_business->address;
            $business->phone       = $uncouponed_business->phone; 
            $business->email       = $uncouponed_business->email;
            $business->description = $uncouponed_business->description;
            $business->coupons     = $coupons;

            array_push($businesses, $business);
        }
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
        $uncouponed_business = R::findOne('businesses', 'id=?', array($id));

        $sql = 'SELECT c.id, c.name, c.expired_date, c.description '.
                'FROM businesses b JOIN coupons c ON c.business_id = b.id WHERE b.id = '.$id;

        $records  = R::getAll($sql);
        $coupons  = R::convertToBeans('coupons', $records);
        $business = R::dispense('business');

        $business->id          = $id;
        $business->name        = $uncouponed_business->name;
        $business->address     = $uncouponed_business->address;
        $business->phone       = $uncouponed_business->phone; 
        $business->email       = $uncouponed_business->email;
        $business->description = $uncouponed_business->description;
        $business->coupons     = $coupons;

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
        $phone       = $input->phone;
        $email       = $input->email;
        $description = $input->description;
        $address     = $input->address;

        if ($name == '' || $address == '')
            response_json_error($app, 400, 'Bad Request');
        else
        {
            $business = R::dispense('businesses');

            $business->name        = $name;
            $business->address     = $address;
            $business->description = $description;
            $business->email       = $email;
            $business->phone       = $phone;

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
// PUT BUSINESSES
$app->put('/businesses/:id', function($id) use ($app) {
    try
    {
        $request = $app->request();
        $body    = $request->getBody();
        $input   = json_decode($body);

        $name        = $input->name;
        $phone       = $input->phone;
        $email       = $input->email;
        $description = $input->description;
        $address     = $input->address;

        if ($name == '' || $address == '')
            response_json_error($app, 400, 'Bad Request');
        else
        {
            $business = R::findOne('businesses', 'id=?', array($id));

            $business->name        = $name;
            $business->address     = $address;
            $business->description = $description;
            $business->email       = $email;
            $business->phone       = $phone;

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

// GET USERS
$app->get('/users', function() use ($app) {
    try
    {
        $users = R::findAll('users');
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($users));
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});

// GET USER
$app->get('/users/:id', function($id) use ($app) {
    try
    {
        $users = R::findOne('users', 'id=?', array($id));
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($users));
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});

// POST USERS
$app->post('/users', function() use ($app) {
    $name  = $_POST['name'];
    $email = $_POST['email'];

    if (R::findOne('users', 'email = ?', array($email)))
        echo 'You already subscribed';
    else
    {
        $user = R::dispense('users');
        $user->name  = $name;
        $user->email = $email;

        $id = R::store($user);
        echo 'Subscribed';
    }
});
// PUT USERS
$app->put('/users/:id', function($id) use ($app) {
    try
    {
        $request = $app->request();
        $body    = $request->getBody();
        $input   = json_decode($body);

        $user = R::findOne('users', 'id=?', array($id));

        $user->name  = $input->name;
        $user->email = $input->email;

        $id = R::store($user);

        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($user));
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});
// DELETE USER
$app->delete('/users/:id', function($id) use ($app) {
    try
    {
        $user = R::findOne('users', 'id=?', array($id));
        R::trash($user);
        $app->response()->status(204);
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});

// EMAIL
$app->post('/email', function() use ($app) {
    $from_email = $_POST['from_email'];
    $to_email   = $_POST['to_email'];
    $subject    = $_POST['subject'];
    $content    = $_POST['content'];

    $mail = new PHPMailer();
    $mail->IsSMTP();

    $mail->SMTPDebug  = 2;
    $mail->Debugoutput = 'html';

    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPSecure = 'tls';
    $mail->SMTPAuth = true;
    $mail->Username = "foobar043@gmail.com";
    $mail->Password = 'testing12345'; // Enter password here

    //Set who the message is to be sent from
    $mail->SetFrom($from_email, 'First Last');
    //Set the subject line
    $mail->Subject = $subject;

    // Email Body
    $mail->MsgHTML($content); 

    //Set who the message is to be sent to
    if (strcmp(gettype($to_email), 'string') == 0)
    {
        $mail->AddAddress($to_email, 'John Doe');
        //Send the message, check for errors
        if(!$mail->Send())
            echo "Mailer Error: " . $mail->ErrorInfo;
        else
            echo "Message sent!";
    }
    else
    {
        foreach ($to_email as $email)
        {
            $mail->ClearAddresses();
            $mail->AddAddress($email, 'John Doe');
            //Send the message, check for errors
            if(!$mail->Send())
                echo "Mailer Error: " . $mail->ErrorInfo;
            else
                echo "Message sent!";
        }
    }
});

// GET BANNERS
$app->get('/banners', function() use ($app) {
    try
    {
        $banners = R::findAll('banners');
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($banners));
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});

// GET BANNER
$app->get('/banners/:id', function($id) use ($app) {
    try
    {
        $banner = R::findOne('banners', 'id=?', array($id));
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode(R::exportAll($banner));
    }
    catch (Exception $e)
    {
        response_json_error($app, 400, $e->getMessage());
    }
});

// POST BANNERS
$app->post('/banners', function() use ($app) {
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
            $banner = R::dispense('banners');
            $banner->path = $file_path;
            $id = R::store($banner);
            $app->response()->header('Content-Type', 'application/json');
            echo json_encode(R::exportAll($banner));
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    catch (Exception $e) {
        echo $e->getMessage();
    }
});

// PUT BANNERS
$app->post('/banners/:id', function($id) use ($app) {
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
            $banner = R::findOne('banners', 'id=?', array($id));
            $banner->path = $file_path;
            $id = R::store($banner);
            $app->response()->header('Content-Type', 'application/json');
            echo json_encode(R::exportAll($banner));
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    catch (Exception $e) {
        echo $e->getMessage();
    }
});

$app->delete('/banners/:id', function($id) use ($app) {
    try 
    {
        $banner = R::findOne('banners', 'id=?', array($id));
        R::trash($banner);
        $app->response()->status(204);
    }
    catch (Exception $e) {
        echo $e->getMessage();
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
