<?php

class Session {

    // VARS

    public static $ip = '127.0.0.1';
    public static $mode = 0; // 0 - web, 1 - call, 2 - api
    public static $status_access = 0;
    public static $ts = 0;
    public static $tz = 0;
    public static $token = '';
    public static $user_id = 0;

    // INIT

    public static function init($mode = 0, $data = []) {
        self::$mode = $mode;
        self::$ts = time();
        return $mode == 2 ? self::session_api($data) : [];
    }

    private static function session_api($data) {
        // vars
        self::$token = $data['token'] ?? '';
        // query
        $q = DB::query("SELECT user_id, status_access, token, tz FROM sessions WHERE token='".self::$token."' LIMIT 1;") or die (DB::error());
        $row = DB::fetch_row($q);
        // validate
        if (!$row) return error_response(1005, 'User authorization failed: invalid access token.');
        // vars
        self::$user_id = $row['user_id'];
        self::$status_access = $row['status_access'];
        self::$ip = flt_input($_SERVER['REMOTE_ADDR']);
        self::$tz = $row['tz'];
        // output
        return 'ok';
    }

    // AUTH

    public static function phone_code($data) {
        // vars
        $phone = isset($data['phone']) ? preg_replace('~[^\d]+~', '', $data['phone']) : 0;
        // validate
        if (!$phone) return error_response(1003, 'One of the parameters was missing or was passed in the wrong format.', ['phone' => 'empty field']);
        // code
        $code = generate_rand_str(6, 'decimal');
        $user_id = User::user_get_or_create($phone);
        // send
        DB::query("UPDATE users SET phone_code='".$code."', phone_attempts_sms=phone_attempts_sms+1, phone_attempts_code='0' WHERE user_id='".$user_id."' LIMIT 1;") or die (DB::error());
        // output
        return [
            'code' => $code,
            'message' => 'Ваш код подтверждения: '.$code.'. Наберите его в поле ввода.'
        ];
    }

    public static function phone_confirm($data) {
        // vars
        $phone = isset($data['phone']) ? preg_replace('~[^\d]+~', '', $data['phone']) : 0;
        $code = isset($data['code']) && is_numeric($data['code']) ? $data['code'] : 0;
        // error (empty)
        if (!$phone && !$code) return error_response(1003, 'One of the parameters was missing or was passed in the wrong format.', ['phone' => 'empty field', 'code' => 'empty field']);
        if (!$phone) return error_response(1003, 'One of the parameters was missing or was passed in the wrong format.', ['phone' => 'empty field']);
        if (!$code) return error_response(1003, 'One of the parameters was missing or was passed in the wrong format.', ['code' => 'empty field']);
        // check
        $q = DB::query("SELECT user_id, status_access, phone_code, phone_attempts_code, last_login, blocked FROM users WHERE phone='".$phone."' LIMIT 1;") or die (DB::error());
        $row = DB::fetch_row($q);
        // error (unregistered & blocked)
        if (!$row) return error_response(1004, 'User with this phone is not found', ['phone' => 'user is not registered']);
        if ($row['blocked']) return error_response(1005, 'User is blocked, contact our administrator to clarify the details', ['phone' => 'user is blocked']);
        // error (login attempts)
        $attempts = LOGIN_ATTEMPTS - self::phone_attempts_code($row['user_id'], $row['last_login'], $row['phone_attempts_code']);
        if (!$attempts) return error_response(1005, 'Number of invalid code attempts has been exceeded for this user, please try again later.', ['code' => 'exceeded error limit, please try later']);
        // error (code)
        if ($row['phone_code'] != $code) {
            DB::query("UPDATE users SET phone_attempts_code=phone_attempts_code+1, last_login='".self::$ts."' WHERE user_id='".$row['user_id']."' LIMIT 1;") or die (DB::error());
            return error_response(1005, 'Invalid phone code, number of remaining attempts is '.$attempts.'.', ['code' => 'invalid phone code']);
        }
        // vars
        self::$user_id = $row['user_id'];
        self::$status_access = $row['status_access'];
        self::$ts = time();
        self::$tz = 180;
        // update
        return self::session_create();
    }

    private static function session_create() {
        // token
        self::$token = generate_rand_str(40);
        // update
        $query = "UPDATE users SET phone_attempts_code='0', last_login='".self::$ts."' WHERE user_id='".self::$user_id."' LIMIT 1;";
        $query .= "INSERT INTO sessions (user_id, status_access, token, tz, created, logged) VALUES ('".self::$user_id."', '".self::$status_access."', '".self::$token."', '".self::$tz."', '".self::$ts."', '".self::$ts."');";
        DB::query($query) or die (DB::error());
        // output
        return ['token' => self::$token];
    }

    private static function phone_attempts_code($user_id, $last_login, $attempts) {
        // clear
        if ((self::$ts - $last_login) > 3600) {
            DB::query("UPDATE users SET login_attempts='0' WHERE user_id='".$user_id."' LIMIT 1;") or die (DB::error());
            return 0;
        }
        // default
        return $attempts;
    }

    public static function logout() {
        // query
        DB::query("DELETE FROM sessions WHERE token='".self::$token."' LIMIT 1;") or die (DB::error());
        // output (api)
        if (self::$mode == 2) return ['message' => 'logout success'];
        // output (web)
        header('Location: /');
        exit();
    }

}
