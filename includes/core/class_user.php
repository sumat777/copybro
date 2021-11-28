<?php

class User
{

    // GENERAL

    public static function user_info($data)
    {
        // vars
        $user_id = isset($data['user_id']) && is_numeric($data['user_id']) ? $data['user_id'] : 0;
        $phone = isset($data['phone']) ? preg_replace('~[^\d]+~', '', $data['phone']) : 0;
        // where
        if ($user_id) $where = "user_id='" . $user_id . "'";
        else if ($phone) $where = "phone='" . $phone . "'";
        else return [];
        // info
        // AV 20211120 странно, но в оригинальной версии вообще не было phone - 
        $q = DB::query("SELECT user_id, first_name, last_name, middle_name, email, phone, gender_id, count_notifications FROM users WHERE " . $where . " LIMIT 1;") or die(DB::error());
        if ($row = DB::fetch_row($q)) {
            return [
                'id' => (int) $row['user_id'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'middle_name' => $row['middle_name'],
                'gender_id' => (int) $row['gender_id'],
                'email' => $row['email'],
                'phone' => (int) $row['phone'],
                //'phone' => $row['phone'],
                'phone_str' => phone_formatting($row['phone']),
                'count_notifications' => (int) $row['count_notifications']
            ];
        } else {
            return [
                'id' => 0,
                'first_name' => '',
                'last_name' => '',
                'middle_name' => '',
                'gender_id' => 0,
                'email' => '',
                'phone' => '',
                'phone_str' => '',
                'count_notifications' => 0
            ];
        }
    }

    public static function user_update($data)
    {
        // AV 20211120
        // vars
        //
        $user_id = isset($data['user_id']) ? $data['user_id'] : 0;
        // var_dump($user_id);
        if (!$user_id) return false; // если нет идентификатора пользователя, то уходим...
        // * Можно обновлять только поля `first_name`, `last_name`, `middle_name`, `email` и `phone`
        // * Телефон должен очищаться от нецифровых символов (при вводе `+7-900-000-00-00` телефон должен корректно обновляться)
        $phone = isset($data['phone']) ? preg_replace('~[^\d]+~', '', $data['phone']) : 0;
        //var_dump($phone);
        $first_name = isset($data['first_name']) ? $data['first_name'] : '';
        //var_dump($first_name);
        $last_name = isset($data['last_name']) ? $data['last_name'] : '';
        $middle_name = isset($data['middle_name']) ? $data['middle_name'] : '';
        // * Email должен автоматически переводиться в нижний регистр
        $email = isset($data['email']) ? strtolower($data['email']) : '';
        // var_dump($email);
        // * Поля `first_name`, `last_name` и `phone` должны быть непустыми
        if (!$first_name) return false;
        if (!$last_name) return false;
        if (!$phone) return false;
        // * Поле `phone` должно содержать 11 цифр и начинаться с 7
        if ((substr($phone, 0, 1)) != "7") return false;
        if ((strlen($phone)) != 11) return false;
        //
        $update_str =
            "UPDATE users 
        SET 
 first_name = '$first_name'
,last_name = '$last_name'
,middle_name = '$middle_name'
,email = '$email'
,phone = '$phone'
WHERE user_id = $user_id LIMIT 1;";
        //
        //var_dump($update_str);
        //

        $q = DB::query($update_str) or die(DB::error());
        //
        $cnt1 = $q->rowCount();

        //
        // var_dump($cnt1);
        //
        if (!$cnt1) return false; // обновления не случилось, уходим отсюда...
        //

        //
        // * При каждом обновлении профиля в базу должна добавлять запись с уведомлением, что информация обновлена
        /*
--
Имеется ввиду запись в таблицу
Таблица: user_notifications
notification_id Первичный	bigint(19)	
user_id Индекс	bigint(19)	
title	varchar(255)	utf8mb4_general_ci	
description	text	utf8mb4_general_ci	
viewed	tinyint(1)		UNSIGNED
created	int(11)		UNSIGNED
--
*/
        //
        /*
$update_str следует предварительно обработать (заменить кавычки и т.д.)
*/
        //
        $update_str_spec = htmlspecialchars($update_str, ENT_QUOTES);
        //
        $title = "Обновление от: " . date("Y-m-d H:i:s");
        //
        $insert_str =
            "INSERT INTO user_notifications (
 user_id
,title
,description
) 
VALUES(
 $user_id 
,'$title' 
,'$update_str_spec'
)
";
        //
        //var_dump($insert_str);
        //

        DB::query($insert_str) or die(DB::error());
        $notification_id = DB::insert_id();

        // output
        return $notification_id;
    }


    public static function user_get_or_create($phone)
    {
        // validate
        $user = User::user_info(['phone' => $phone]);
        $user_id = $user['id'];
        // create
        if (!$user_id) {
            DB::query("INSERT INTO users (status_access, phone, created) VALUES ('3', '" . $phone . "', '" . Session::$ts . "');") or die(DB::error());
            $user_id = DB::insert_id();
        }
        // output
        return $user_id;
    }


    public static function notifications_get($data)
    {
        // AV 20211120
        // vars
        //
        $user_id = isset($data['user_id']) ? $data['user_id'] : 0;
        // var_dump($user_id);
        if (!$user_id) return false; // если нет идентификатора пользователя, то уходим...
        //* При вызове можем отправить опциональный параметр, чтобы получить список только непрочитанных уведомлений
        $new_only = isset($data['new_only']) ? $data['new_only'] : 0;
        //
        // * У каждого уведомления должен быть заголовок, описание, дата создания и флаг о статусе прочтения
        $select_str =
            "SELECT 
 title
,description
,modirec
,viewed 
FROM user_notifications 
WHERE user_id=$user_id";
        //
        if ($new_only) $select_str .= " AND viewed=0";
        $select_str .= ";";
        //
        // var_dump($select_str);
        //
        // output
        $q = DB::query($select_str) or die(DB::error());
        //
        $rez1 = array();
        $cnt1 = 0;
        //
        while ($row = DB::fetch_row($q)) {
            $cnt1++;
            $rez1[] = $row;
        }
        //
        //var_dump($cnt1);
        //
        //var_dump($rez1);
        //
        return $rez1;
        //

        /*

        if ($row = DB::fetch_row($q)) {
            return [
                'title' => $row['title'],
                'description' => $row['description'],
                'modirec' => $row['modirec'],
                'viewed' => (int) $row['viewed']
            ];
        } else {
            return [
                'title' => '',
                'description' => '',
                'modirec' => '',
                'viewed' => 0
            ];
        }
*/
    }


    public static function notifications_read($data)
    {
        // AV 20211120
        // vars
        //
        $user_id = isset($data['user_id']) ? $data['user_id'] : 0;
        // var_dump($user_id);
        if (!$user_id) return false; // если нет идентификатора пользователя, то уходим...
        // * При вызове читает все уведомления пользователя
        /*
В отличии от notifications_get здесь мы 
- берем только непрочтенные сообщения
- будем ставить отметки о прочтении...
- возвращаем не массив сообщений, а количество прочтенных.
*/
        //
        $select_str =
            "SELECT 
 notification_id 
,title
,description
,modirec
,viewed 
FROM user_notifications 
WHERE user_id=$user_id AND viewed=0;";
        //
        $q = DB::query($select_str) or die(DB::error());
        //
        $rez1 = array();
        $cnt1 = 0;
        $cnt2 = 0;
        //
        while ($row = DB::fetch_row($q)) {
            $cnt1++;
            $rez1[] = $row;
            //$notification_id = $row[$cnt1]['notification_id'];
            $notification_id = $row['notification_id'];
            //
        }
        //
        $update_str =
            "UPDATE user_notifications 
SET 
 viewed = 1 
WHERE user_id=$user_id;";
        //
        //var_dump($update_str);
        //

        $q = DB::query($update_str) or die(DB::error());
        //
        $cnt2 = $q->rowCount();
        return $cnt2;
    }

    // TEST

    public static function owner_info()
    {
        // your code here ...
    }

    public static function owner_update($data = [])
    {
        // your code here ...
    }
}
