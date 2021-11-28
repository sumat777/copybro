<?php

class User {

    // GENERAL

    public static function user_info($data) {
        // vars
        $user_id = isset($data['user_id']) && is_numeric($data['user_id']) ? $data['user_id'] : 0;
        $phone = isset($data['phone']) ? preg_replace('~[^\d]+~', '', $data['phone']) : 0;
        // where
        if ($user_id) $where = "user_id='".$user_id."'";
        else if ($phone) $where = "phone='".$phone."'";
        else return [];
        // info
        $q = DB::query("SELECT user_id, first_name, last_name, middle_name, email, gender_id, count_notifications FROM users WHERE ".$where." LIMIT 1;") or die (DB::error());
        if ($row = DB::fetch_row($q)) {
            return [
                'id' => (int) $row['user_id'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'middle_name' => $row['middle_name'],
                'gender_id' => (int) $row['gender_id'],
                'email' => $row['email'],
                'phone' => (int) $row['phone'],
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

    public static function user_get_or_create($phone) {
        // validate
        $user = User::user_info(['phone' => $phone]);
        $user_id = $user['id'];
        // create
        if (!$user_id) {
            DB::query("INSERT INTO users (status_access, phone, created) VALUES ('3', '".$phone."', '".Session::$ts."');") or die (DB::error());
            $user_id = DB::insert_id();
        }
        // output
        return $user_id;
    }

    // TEST

    public static function owner_info() {
        // your code here ...
    }

    public static function owner_update($data = []) {
        // your code here ...
    }

}
