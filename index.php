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
    $app->render('app/index.html');
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
    // category
    try 
    {
        $raw_category = R::findOne('categories', 'id=?', array($id));

        $sql = 'SELECT DISTINCT m.* FROM categories c '.
               'JOIN services s ON s.category_id = c.id '.
               'JOIN machines m ON s.machine_id = m.id '.
               'WHERE c.id = '.$id;
        $records = R::getAll($sql);
        $bean_machines = R::convertToBeans('machines', $records);

        $machines = [];

        foreach ($bean_machines as $bean_machine)
        {
            $sql = 'SELECT c.* FROM coupons c '.
                   'JOIN machines m on c.machine_id = m.id '.
                   'WHERE m.id = '.$bean_machine->id;
            $records = R::getAll($sql);
            $bean_coupons = R::convertToBeans('coupons', $records);

            $machine = R::dispense('machine');
            $machine->id      = $bean_machine->id;
            $machine->name    = $bean_machine->name;
            $machine->suburb  = $bean_machine->suburb;
            $machine->address = $bean_machine->address;
            $machine->coupons = $bean_coupons;
            array_push($machines, $machine);
        }

        $category = R::dispense('category');
        $category->id = $id;
        $category->name = $raw_category->name;
        $category->machines = $machines;

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
        else response_json_error($app, 404, 'Invalid location and post code');
    }
    else response_json_error($app, 411, 'Ihe query is required at least 3 characters');
});

// GET MACHINE LOCATION
$app->get('/machines/search', function() use ($app) {
    if (strlen($app->request()->get('q')) >= 3)
    {
        $query = $app->request()->get('q');
        $uncategorised_machines = R::find('machines', 'suburb REGEXP ?', array($query));
        $machines = [];
        if (sizeof($uncategorised_machines) > 0)
        {
            foreach ($uncategorised_machines as $uncategorised_machine)
            {
                $sql = 'SELECT c.id, c.name FROM machines m '.
                    'JOIN services s ON s.machine_id = m.id '.
                    'JOIN categories c ON s.category_id = c.id '.
                    'WHERE m.id = '.$uncategorised_machine->id;

                $records = R::getAll($sql);
                $categories = R::convertToBeans('categories', $records);

                $machine = R::dispense('machine');

                $machine->id         = $uncategorised_machine->id;
                $machine->name       = $uncategorised_machine->name;
                $machine->suburb     = $uncategorised_machine->suburb;
                $machine->address    = $uncategorised_machine->address;
                $machine->categories = $categories;

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
        $uncategorised_machines = R::findAll('machines');
        $machines = [];
        foreach ($uncategorised_machines as $uncategorised_machine)
        {
            $sql = 'SELECT c.id, c.name FROM machines m '.
                'JOIN services s ON s.machine_id = m.id '.
                'JOIN categories c ON s.category_id = c.id '.
                'WHERE m.id = '.$uncategorised_machine->id;

            $records = R::getAll($sql);
            $categories = R::convertToBeans('categories', $records);

            $machine = R::dispense('machine');

            $machine->id         = $uncategorised_machine->id;
            $machine->name       = $uncategorised_machine->name;
            $machine->suburb     = $uncategorised_machine->suburb;
            $machine->address    = $uncategorised_machine->address;
            $machine->categories = $categories;

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
        $uncategorised_machine = R::findOne('machines', 'id=?', array($id));

        $sql = 'SELECT c.id, c.name FROM machines m '.
            'JOIN services s ON s.machine_id = m.id '.
            'JOIN categories c ON s.category_id = c.id '.
            'WHERE m.id = '.$id;

        $records = R::getAll($sql);
        $categories = R::convertToBeans('categories', $records);

        $machine = R::dispense('machine');

        $machine->id         = $id;
        $machine->name       = $uncategorised_machine->name;
        $machine->suburb     = $uncategorised_machine->suburb;
        $machine->address    = $uncategorised_machine->address;
        $machine->categories = $categories;

        $app->response()->header('content-type', 'application/json');
        echo json_encode(R::exportall($machine));
    } 
    catch (exception $e)
    {
        response_json_error($app, 400, $e->getmessage());
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
        response_json_error($app, 400, $e->getMessage());
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
              'b.name "business_name", b.address "business_address", b.phone_number, b.email, '.
              'b.description "business_desc "'.
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
              'b.name "business_name", b.address "business_address", b.phone_number "business_phone_number", b.email "business_email", '.
              'b.description "business_desc "'.
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


    //try 
    //{
        //$email= new Email();
        //$email->setTo($to_email);
        //$email->setFrom('11458724@student.uts.edu.au');
        //$email->setSubject($subject);
        //$email->setText($content);
        //$email->send();
    //}
    //catch (Exception $e) 
    //{
        //echo $e->getMessage();
    //}

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

// Run awesome app
$app->run();

function response_json_error($app, $http_code, $msg)
{
    $app->response()->status($http_code);
    $app->response()->header('Content-Type', 'application/json');
    $app->response()->header('X-Status-Reason', $msg);
    echo json_encode(array('error' => $msg));
}
