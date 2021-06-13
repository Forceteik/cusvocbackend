<?php
function getUsers ($connect) {
    $users = mysqli_query($connect, "SELECT * FROM `user_account`");

    $usersList = [];

    while($user = mysqli_fetch_assoc($users)) {
        $data2 = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user_photo WHERE user_account_id ='" . mysqli_real_escape_string($connect, $user['id']) . "' LIMIT 1"));
        $usersList[] = $user + $data2;
    }

    echo json_encode($usersList);
}

function getUser($connect, $id){
    $data2 = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user_photo WHERE user_account_id ='" . mysqli_real_escape_string($connect, $id) . "' LIMIT 1"));
    $user = mysqli_query($connect, "SELECT * FROM `user_account` WHERE id = $id");

    if (mysqli_num_rows($user) === 0) {
        http_response_code(404);
        $res = [
            "status" => false,
            "message" => "User not found"
        ];
        echo json_encode($res);
    }else {
        $user = mysqli_fetch_assoc($user);
        echo json_encode($user + $data2);
    }
}

function getPhoto ($connect, $id) {
    $user = mysqli_query($connect, "SELECT * FROM `user_photo` WHERE user_account_id = $id");

    if (mysqli_num_rows($user) === 0) {
        http_response_code(404);
        $res = [
            "status" => false,
            "message" => "User not found"
        ];
        echo json_encode($res);
    }else {
        $user = mysqli_fetch_assoc($user);
        echo json_encode($user);
    }
}

function addUser($connect, $data, $photoData) {
    $first_name = $data ['first_name'];
    $last_name = $data ['last_name'];
    $gender_id = $data ['gender_id'];
    $details = $data ['details'];
    $nickname = $data ['nickname'];
    $email = $data ['email'];
    $confirmation_code = $data ['confirmation_code'];
    $confirmation_time = $data ['confirmation_time'];
    $popularity = $data ['popularity'];

    mysqli_query($connect, "INSERT INTO `user_account` (`id`, `first_name`, `last_name`, `gender_id`, `details`, `nickname`, `email`, `confirmation_code`, `confirmation_time`, `popularity`) 
    VALUES (NULL, '$first_name', ' $last_name', '$gender_id', '$details', '$nickname', '$email','$confirmation_code', '$confirmation_time', '$popularity')");

    $uploads_dir = __DIR__."/images";
    $tempLink = $photoData ['photo']['tmp_name'];
    $photoDetails = $photoData ['photo']['name'];
    move_uploaded_file($tempLink, "$uploads_dir/$photoDetails");

    $link = "http://d70242yz.beget.tech/images"."/"."$photoDetails";


    $userid = mysqli_insert_id($connect);
    mysqli_query($connect, "INSERT INTO `user_photo` (`id`, `user_account_id`, `link`, `details`, `time_added`, `active`) VALUES (NULL, '$userid', '$link', '$photoDetails', '$confirmation_time', '1')");


    http_response_code(201);

    $res = [
        "status" => true,
        "user_id" => $userid+15,
        "photo_id" => mysqli_insert_id($connect)
    ];

    echo json_encode($res);
}

function updateUser ($connect, $id, $data) {
    $first_name = $data ['first_name'];
    $last_name = $data ['last_name'];
    $gender_id = $data ['gender_id'];
    $details = $data ['details'];
    $nickname = $data ['nickname'];
    $email = $data ['email'];
    $confirmation_code = $data ['confirmation_code'];
    $confirmation_time = $data ['confirmation_time'];
    $popularity = $data ['popularity'];

    mysqli_query($connect, "UPDATE `user_account` SET `first_name` = '$first_name', `last_name` = '$last_name', `gender_id` = '$gender_id', `details` = '$details', `nickname` = '$nickname', `email` = '$email', 
                          `confirmation_code` = '$confirmation_code', `confirmation_time` = '$confirmation_time', `popularity` = '$popularity' WHERE `user_account`.id = '$id'");


    http_response_code(200);

    $res = [
        "status" => true,
        "message" => "User is updated"
    ];

    echo json_encode($res);

}

function deleteUser ($connect, $id) {
    $dif = $id-29;

    mysqli_query($connect, "DELETE FROM `grade` WHERE `grade`.`user_account_id_given` = '$id'");
    mysqli_query($connect, "DELETE FROM `user_photo` WHERE `user_photo`.`id` = '$dif'");
    mysqli_query($connect, "DELETE FROM `user_account` WHERE `user_account`.`id` = '$id'");

    http_response_code(200);

    $res = [
        "status" => true,
        "message" => "User is deleted"
    ];

    echo json_encode($res);
}

function addLike($connect, $data, $id) {
    $user_account_id_received = $data ['user_account_id_received'];
    $grade = $data ['grade'];

    mysqli_query($connect, "INSERT INTO `grade` (`id`, `user_account_id_given`, `user_account_id_received`, `grade`) VALUES (NULL, '$id', '$user_account_id_received', '$grade')");

    http_response_code(201);

    $res = [
        "status" => true,
        "like_id" => mysqli_insert_id($connect)
    ];

    echo json_encode($res);
}

function getLike ($connect, $id) {

    $likes = mysqli_query($connect, "SELECT * FROM `grade` WHERE user_account_id_received = $id");

    $likesList = [];

    while($like = mysqli_fetch_assoc($likes)) {
        $data3 = mysqli_fetch_assoc(mysqli_query($connect, "SELECT acc.*, ph.link FROM user_account as acc LEFT JOIN user_photo as ph ON (acc.id = ph.user_account_id) WHERE acc.id = '" . mysqli_real_escape_string($connect, $like['user_account_id_given']) . "' LIMIT 1"));
        $likesList[] = $like + $data3;
    }

    echo json_encode($likesList);
}

function addBlockUser($connect, $data, $id) {
    $dif = $id-29;

    mysqli_query($connect, "DELETE FROM `grade` WHERE `grade`.`user_account_id_given` = '$id'");
    mysqli_query($connect, "DELETE FROM `user_photo` WHERE `user_photo`.`id` = '$dif'");
    mysqli_query($connect, "DELETE FROM `user_account` WHERE `user_account`.`id` = '$id'");

    http_response_code(200);

    $res = [
        "status" => true,
        "message" => "User is deleted"
    ];

    echo json_encode($res);
}

function auth ($connect, $data)
{
    $login = $data ['login'];
    $password = $data ['password'];
    $fail = [
        "authenticated" => false,
        "message" => "Login or password incorrect"
    ];
    if(isset($login, $password))
    {

        $data2 = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id, confirmation_code FROM user_account WHERE email ='" . mysqli_real_escape_string($connect, $login) . "' LIMIT 1"));


        if (empty($data2)) {
            echo json_encode($fail);
            exit();
        }


        if($data2['confirmation_code'] === $password)
        {
            $id = $data2['id'];
            $succes = [
                "authenticated" => true,
                "id" => $id
            ];
            echo json_encode($succes);

        }
        else
        {
            echo json_encode($fail);
        }
    }

}
