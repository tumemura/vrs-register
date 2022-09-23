<?php

namespace App\Http\Controllers;

use Throwable;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;


class MainController extends Controller
{
    public function index(Request $request)
    {
        $error = '';
        $coupon_code = '';
        
        if (Session::has('error')) {
            $error = Session::get('error');
        }
        if (Session::has('coupon_code')) {
            $coupon_code = Session::get('coupon_code');
        }
        
        if (!empty($error) || !empty($coupon_code)) {
            Session::flush();
        }
        
        $request->session()->regenerateToken();

        $params = [
                'error' => $error,
                'coupon_code' => $coupon_code,
       ];
        return view('login', $params);
    }

    public function spindex(Request $request)
    {
        $error = '';
        $coupon_code = '';
        
        if (Session::has('error')) {
            $error = Session::get('error');
        }
        if (Session::has('coupon_code')) {
            $coupon_code = Session::get('coupon_code');
        }
        
        if (!empty($error) || !empty($coupon_code)) {
            Session::flush();
        }

        $request->session()->regenerateToken();
        
        $params = [
                'error' => $error,
                'coupon_code' => $coupon_code,
       ];
        return view('splogin', $params);
    }
    
    public function status($small = false)
    {
        $item = DB::table('site')->get()->first();
        
        $data = DB::table('frames');
        $data->whereRaw('start_at > DATE_ADD(CURRENT_DATE,interval 1 day)');
        $data->where('vaccine_id', '14'); // ファイザー４回目
        $data->where('category_id', 1); // Web予約
        $data->whereColumn('vaccine_count', '>', 'reservation_count');
        $data->orderBy('start_at');
        $frames = $data->get();
        $firstReservationDate = "";
        foreach ($frames as $frame) {
            $firstReservationDate = date("Y/m/d", strtotime($frame->start_at));
            break;
        }
           
        return view('status', [
            'reservation_available'=>$item->web_vaccine_14_reservation_limit,
            'reservation_available_today'=>$item->web_vaccine_14_today_reservation_limit,
            'first_reservation_date'=>$firstReservationDate,
            'small'=>$small
        ]);
    }

    public function status2($small = false)
    {
        $item = DB::table('site')->get()->first();
        
        $data = DB::table('frames');
        $data->whereRaw('start_at > DATE_ADD(CURRENT_DATE,interval 1 day)');
        $data->where('vaccine_id', '24'); // モデルナ４回目
        $data->where('category_id', 1); // Web予約
        $data->whereColumn('vaccine_count', '>', 'reservation_count');
        $data->orderBy('start_at');
        $frames = $data->get();
        $firstReservationDate = "";
        foreach ($frames as $frame) {
            $firstReservationDate = date("Y/m/d", strtotime($frame->start_at));
            break;
        }
           
        return view('status', [
            'reservation_available'=>$item->web_vaccine_24_reservation_limit,
            'reservation_available_today'=>$item->web_vaccine_24_today_reservation_limit,
            'first_reservation_date'=>$firstReservationDate,
            'small'=>$small
        ]);
    }
    
    
    private function getDosageInterval($vaccine_id)
    {
        $vaccines = DB::table('vaccines')
        ->where('vaccine_id', $vaccine_id)
        ->get();
        return $vaccines->first()->dosage_interval;
    }
    
  
    // 前回接種日を計算する関数
    private function getPrevDosageAt($patient_id, $vaccine_id)
    {
        $patients = DB::table('patients')
            ->where('patient_id', $patient_id)
            ->first();

        if (($vaccine_id -1)%10 == 2) {
            // 3回目接種のみ、２回目接種日の入力があればそれを使用する
            if (!empty($patients->second_dose_date)) {
                return $patients->second_dose_date;
            }
        } else if (($vaccine_id -1)%10 == 3) {
            if (!empty($patients->third_dose_date)) {
                return $patients->third_dose_date;
            }
        }

        if ($vaccine_id %10 > 1) {
            foreach ([10,20] as $vaccine) {
                $target = ($vaccine_id - 1)%10 + $vaccine;
 
                $reservations = DB::table('reservations')
                ->join('frames', 'reservations.frame_id', '=', 'frames.frame_id')
                ->where('patient_id', $patient_id)
                ->where('vaccine_id', $target)
                ->where('status_code', '!=', 3)
                ->get();
                if (!$reservations->isEmpty()) {
                    $dateTime = explode(" ", $reservations->first()->start_at);
                    return $dateTime[0]." 00:00:00";
                }
            }
        }



        return "";
    }


    public function afterFiveMonth($date)
    {
        $current = new DateTime($date);
        $item = DB::select(DB::raw("select date_add('{$date}',interval 5 month) as five_month"));
        $next = new DateTime($item[0]->five_month);

        if ($current->format('d') != $next->format('d')) {
            $next->modify('+1 day');
        }
        return $next->format("Y-m-d H:i:s");
    }


    public function afterSixMonth($date)
    {
        $current = new DateTime($date);
        $item = DB::select(DB::raw("select date_add('{$date}',interval 6 month) as six_month"));
        $next = new DateTime($item[0]->six_month);

        if ($current->format('d') != $next->format('d')) {
            $next->modify('+1 day');
        }
        return $next->format("Y-m-d H:i:s");
    }

    public function afterSevenMonth($date)
    {
        $current = new DateTime($date);
        $item = DB::select(DB::raw("select date_add('{$date}',interval 7 month) as seven_month"));
        $next = new DateTime($item[0]->seven_month);

        if ($current->format('d') != $next->format('d')) {
            $next->modify('+1 day');
        }
        return $next->format("Y-m-d H:i:s");
    }

    public function saveDoseDate(Request $request) {
        $patient_id = Session::get('patient_id');
        if ($request->third_dose_date) {
            DB::table('patients')
                ->where('patient_id', $patient_id)
                ->update(['third_dose_date'=>$request->third_dose_date]);
            return redirect('/calendar/'.$request->vaccine_id);
        } else if ($request->second_dose_date) {
            DB::table('patients')
            ->where('patient_id', $patient_id)
            ->update(['second_dose_date'=>$request->second_dose_date]);
            return redirect('/calendar/'.$request->vaccine_id);
        }
        return redirect('/mypage');
    }

    public function enterDoseDate($vaccine_id) {
        return view('enter_dose_date',['vaccine_id' => $vaccine_id]);
    }

    public function calendar($vaccine_id)
    {
        $patient_id = Session::get('patient_id');

        $patient = DB::table('patients')->where('patient_id', $patient_id)->get()->first();
     
        // 前回接種日を取得する
        $prev_dosage = $this->getPrevDosageAt($patient_id, $vaccine_id);
        
        $frames = DB::table('frames');
        $frames->selectRaw("DATE_FORMAT(start_at, '%Y-%m-%d') AS date, sum(vaccine_count) as total, sum(reservation_count) as used");
        $frames->where('category_id', $patient->category_id);
        $frames->where('vaccine_id', $vaccine_id);
        
        $site = DB::table('site')->get()->first();

        if (empty($prev_dosage)) {
            if ($vaccine_id % 10 == 1) {
                $frames->whereRaw('start_at > DATE_ADD(CURRENT_DATE,interval 1 day)');
            } else {
                if ($vaccine_id % 10 == 2) {
                    Session::flash('error', '１回目接種データがないため予約できません');
                    return redirect("/mypage");
                } else if ($vaccine_id % 10 == 3) {
                    return redirect("/enter_dose_date/".$vaccine_id);
                } else {
                    return redirect("/enter_dose_date/".$vaccine_id);
                }
            }
        } else {
            $dosage_interval = $this->getDosageInterval($vaccine_id);
            if ($dosage_interval == 150) {
                $frames->whereRaw("start_at >= '".$this->afterFiveMonth($prev_dosage)."'");
            } elseif ($dosage_interval == 180) {
                $frames->whereRaw("start_at >= '".$this->afterSixMonth($prev_dosage)."'");
            } elseif ($dosage_interval == 210) {
                $frames->whereRaw("start_at >= '".$this->afterSevenMonth($prev_dosage)."'");
            } else {
                $frames->whereRaw("start_at >= date_add('{$prev_dosage}',interval {$dosage_interval} day)");
            }

            if ((floor($vaccine_id/10) == 1 && $site->web_vaccine_14_today_reservation_limit > 0)
                 || (floor($vaccine_id/10) == 2 && $site->web_vaccine_24_today_reservation_limit > 0)) {
                $frames->whereRaw('start_at > now()');
            } else {
                $frames->whereRaw('start_at > DATE_ADD(CURRENT_DATE,interval 1 day)');
            }
        }
          
        $frames->groupByRaw("DATE_FORMAT(start_at,'%Y%m%d')");
        $summary = $frames->get();
        
        return view('calendar', [
            'summary'=>$summary,
            'vaccine_id'=>$vaccine_id,
            'start_date'=> Session::get("start_date", date('Y-m-d')),
        ]);
    }
    
    public function login(Request $request)
    {
        $items = DB::table('patients')
        ->where('municipal_code', $request->municipal_code)
        ->where('coupon_code', $request->coupon_code)
        ->where('date_of_birth', $request->date_of_birth)
        ->get();
        
        if (!$items->isEmpty()) {
            // ログイン成功
            $patient = $items->first();

            if ($patient->category_id != 1) {
                // WEB窓口でWEB以外の場合は、強制的にWEBにカテゴリーを変更
                $items = DB::table('patients')
                ->where('municipal_code', $request->municipal_code)
                ->where('coupon_code', $request->coupon_code)
                ->where('date_of_birth', $request->date_of_birth)
                ->update(['category_id'=>1]);
            }

            Session::put('patient_id', $patient->patient_id);
            Session::put('mode', '');
            return redirect('/mypage');
        }
        
        $items = DB::table('patients')
        ->where('municipal_code', $request->municipal_code)
        ->where('coupon_code', $request->coupon_code)
        ->get();
        
        if (!$items->isEmpty()) {
            // パスワードエラー
            return redirect()->back()->withInput()->withErrors('生年月日が登録された情報と異なります');
        }
        
        $site = DB::table('site')->get()->first();
        if ($site->web_vaccine_14_today_reservation_limit <= 0
            && $site->web_vaccine_14_reservation_limit <= 0
            && $site->web_vaccine_24_today_reservation_limit <= 0
            && $site->web_vaccine_24_reservation_limit <= 0
            ) {
            // 空き枠がない時は新規登録を不可にする
            return redirect()->back()->withInput()->withErrors('予約枠に空きが無いため新規登録はできません');
        }
        
        if (env('LOCATION', '') == 'HIGASHINAEBO') {
            $params = [
                'municipal_code' => $request->municipal_code,
                'coupon_code' => $request->coupon_code,
                'date_of_birth' => $request->date_of_birth,
                'category_id' => 1,
                'office' => '',
                'first_name' => '',
                'last_name' => '',
                'from' => '/'
            ];
            return view('step2', $params);
        }

        // 新規登録
        $params = [
            'municipal_code' => $request->municipal_code,
            'coupon_code' => $request->coupon_code,
            'date_of_birth' => $request->date_of_birth
        ];
        return view('step1', $params);
    }
    

    public function splogin(Request $request)
    {
        $items = DB::table('patients')
        ->where('municipal_code', $request->municipal_code)
        ->where('coupon_code', $request->coupon_code)
        ->where('date_of_birth', $request->date_of_birth)
        ->get();
        
        if (!$items->isEmpty()) {
            // 接種券番号 + 誕生日チェックの通過
            $patient = $items->first();

            if ($patient->category_id != 3) {
                // 専用窓口で医療従事者以外の場合は、強制的に医療従事者にカテゴリーを変更
                $items = DB::table('patients')
                ->where('municipal_code', $request->municipal_code)
                ->where('coupon_code', $request->coupon_code)
                ->where('date_of_birth', $request->date_of_birth)
                ->update(['category_id'=>3]);
            }

        
            Session::put('patient_id', $patient->patient_id);
            Session::put('mode', 'sp');
            return redirect('/mypage');
        }
        
        $items = DB::table('patients')
        ->where('municipal_code', $request->municipal_code)
        ->where('coupon_code', $request->coupon_code)
        ->get();
        
        if (!$items->isEmpty()) {
            return redirect()->back()->withInput()->with('error', '生年月日が登録された情報と異なります');
        }
        
        return redirect('/cc_missing', 307)->withInput();
    }
    

    public function logout()
    {
        $redirect_to = '/';

        if (Session::has('mode') && Session::get('mode') == 'sp') {
            $redirect_to = '/sp';
        }

        Session::flush();
        return redirect(env('TOP_URL', $redirect_to));
    }
    
    public function frame($vaccine_id, $date)
    {
        $patient_id = Session::get('patient_id');
        $patient = DB::table('patients')->where('patient_id', $patient_id)->get()->first();


        $sqldate = $date . ' 00:00:00';
        
        $query = DB::table('frames');
        $query->where('start_at', '>', $sqldate);
        $query->where('start_at', '<', DB::raw("DATE_ADD('{$sqldate}',interval 1 day)"));
        $query->where('category_id', $patient->category_id); // 一般（WEB)
        $query->where('vaccine_id', $vaccine_id);
        $query->whereColumn('vaccine_count', '>', 'reservation_count');

        $site = DB::table('site')->get()->first();
        // 当日予約が可能な場合は当日の予約可否もカレンダー上に表示する
        if ((floor($vaccine_id/10) == 1 && $site->web_vaccine_14_today_reservation_limit > 0)
            || (floor($vaccine_id/10) == 2 && $site->web_vaccine_24_today_reservation_limit > 0)) {
            $query->whereRaw('start_at > now()');
        } else {
            $query->whereRaw('start_at > DATE_ADD(CURRENT_DATE,interval 1 day)');
        }
            
        $query->orderBy('start_at');
        $frames = $query->get();
    
        return view('frame', ['date'=>$date, 'frames'=>$frames]);
    }
    
    public function step1(Request $request)
    {
        // 新規登録
        $params = [
            'municipal_code' => $request->municipal_code,
            'coupon_code' => $request->coupon_code,
            'date_of_birth' => $request->date_of_birth
        ];
        return view('step1', $params);
    }

    public function step1s(Request $request)
    {
        // 登録情報検索
        $params = [
            'municipal_code' => $request->municipal_code,
            'coupon_code' => $request->coupon_code,
            'date_of_birth' => $request->date_of_birth,
            'category_id'=>$request->category_id,
            'office'=>$request->office,
            'from'=>$request->from
        ];
        return view('step1s', $params);
    }

    public function step2(Request $request)
    {
        $params = [
            'municipal_code' => $request->municipal_code,
            'coupon_code' => $request->coupon_code,
            'date_of_birth' => $request->date_of_birth,
            'category_id'=>$request->category_id,
            'office'=>$request->office,
            'first_name'=>$request->first_name,
            'last_name'=>$request->last_name,
            'from'=>$request->from
        ];
        return view('step2', $params);
    }

    public function step1c()
    {
        return view('step1c');
    }

    public function step1n()
    {
        return view('step1n');
    }


    public function cc_missing(Request $request)
    {
        $request->session()->regenerateToken();

        $params = [
            'municipal_code' => $request->municipal_code,
            'coupon_code' => $request->coupon_code,
            'date_of_birth' => $request->date_of_birth
        ];
        return view('cc_missing', $params);
    }


    public function cc_nodata(Request $request)
    {
        return view(
            'cc_nodata',
            [
            'municipal_code' => $request->municipal_code,
            'coupon_code' => $request->coupon_code,
            'date_of_birth' => $request->date_of_birth,
            'office'=>$request->office,
            'first_name'=>$request->first_name,
            'last_name'=>$request->last_name
        ]
        );
    }

    public function cc_update(Request $request)
    {
        $status_code  = 307;

        $first_name = "";
        $last_name = "";

        if ($request->first_name != "" || $request->last_name != "") {
            // 名前検索

            if ($request->first_name == "") {
                Session::flash('error', 'お名前(姓)をご入力ください');
                return back($status_code)->withInput();
            } elseif ($request->last_name == "") {
                Session::flash('error', 'お名前(名)をご入力ください');
                return back($status_code)->withInput();
            }

            $first_name = $request->first_name;
            $last_name = $request->last_name;

            $patients = DB::table('patients')
                ->where('last_name', $request->last_name)
                ->where('first_name', $request->first_name)
                ->where('date_of_birth', $request->date_of_birth)
                ->get();
        } elseif ($request->phone != "") {
            // 電話番号検索
            $patients = DB::table('patients')
                ->where('phone', $request->phone)
                ->where('date_of_birth', $request->date_of_birth)
                ->get();
        } else {
            Session::flash('error', 'お名前または電話番号のどちらかをご入力ください');
            return back($status_code)->withInput();
        }

        Session::put('mode', 'sp');

        if ($patients->isEmpty()) {
            return view(
                'cc_nodata',
                [
                    'municipal_code' => $request->municipal_code,
                    'coupon_code' => $request->coupon_code,
                    'date_of_birth' => $request->date_of_birth,
                    'office'=>$request->office,
                    'first_name'=>$request->first_name,
                    'last_name'=>$request->last_name
                ]
            );
        }


        $patient = $patients->first();

        if (strlen($patient->coupon_code) == 10) {
            Session::flash('error', 'お客様の情報は既に別の接種券番号に紐づいています。接種券番号を再度お確かめください');
            return back($status_code)->withInput();
        }


        $comment = $patient->comment;
        if (!empty($comment)) {
            $comment .= ",";
        }
        $comment .= "事業所｜".$request->office;

        // 接種券番号と市町村コードを更新する
        DB::table('patients')->where('patient_id', $patient->patient_id)->update(
            ['municipal_code'=>$request->municipal_code,
            'coupon_code'=>$request->coupon_code,
            'category_id'=>3,
            'comment'=>$comment
        ]
        );
        Session::put('patient_id', $patient->patient_id);

  

        return view('cc_update');
    }
    

    public function step1r(Request $request)
    {
        $status_code  = 307;

        $first_name = "";
        $last_name = "";

        if ($request->first_name != "" || $request->last_name != "") {
            // 名前検索

            if ($request->first_name == "") {
                Session::flash('error', 'お名前(姓)をご入力ください');
                return back($status_code)->withInput();
            } elseif ($request->last_name == "") {
                Session::flash('error', 'お名前(名)をご入力ください');
                return back($status_code)->withInput();
            }

            $first_name = $request->first_name;
            $last_name = $request->last_name;

            $patients = DB::table('patients')
                ->where('last_name', $request->last_name)
                ->where('first_name', $request->first_name)
                ->where('date_of_birth', $request->date_of_birth)
                ->get();
        } elseif ($request->phone != "") {
            // 電話番号検索
            $patients = DB::table('patients')
                ->where('phone', $request->phone)
                ->where('date_of_birth', $request->date_of_birth)
                ->get();
        } else {
            Session::flash('error', 'お名前または電話番号のどちらかをご入力ください');
            return back($status_code)->withInput();
        }


        if ($patients->isEmpty()) {
            return redirect('/step1n');
        }

        $patient = $patients->first();

        if (strlen($patient->coupon_code) == 10) {
            Session::flash('error', 'お客様の情報は既に別の接種券番号に紐づいています。接種券番号を再度お確かめください');
            return back($status_code)->withInput();
        }

        // 接種券番号と市町村コードを更新する
        DB::table('patients')->where('patient_id', $patient->patient_id)->update(
            ['municipal_code'=>$request->municipal_code,
            'coupon_code'=>$request->coupon_code,
            'category_id'=>1
        ]
        );
        Session::put('mode', '');
        Session::put('patient_id', $patient->patient_id);
 

        return redirect('step1c');
    }

    public function step4(Request $request)
    {
        $params = [
            'municipal_code' => $request->municipal_code,
            'coupon_code' => $request->coupon_code,
            'date_of_birth' => $request->date_of_birth,
            'first_name'=>$request->first_name,
            'last_name'=>$request->last_name,
            'category_id'=>$request->category_id,
            'office'=>$request->office,
            'second_dose_date'=>$request->second_dose_date,
            'third_dose_date'=>$request->third_dose_date
        ];
        return view('step4', $params);
    }
    
    public function step5(Request $request)
    {
        $params = [
            'municipal_code' => $request->municipal_code,
            'coupon_code' => $request->coupon_code,
            'date_of_birth' => $request->date_of_birth,
            'first_name'=>$request->first_name,
            'last_name'=>$request->last_name,
            'second_dose_date'=>$request->second_dose_date,
            'third_dose_date'=>$request->third_dose_date,
            'office'=>$request->office,
            'category_id'=>$request->category_id,
        ];
        return view('step5', $params);
    }
    
    public function step6(Request $request)
    {
        $params = [
            'municipal_code' => $request->municipal_code,
            'coupon_code' => $request->coupon_code,
            'date_of_birth' => $request->date_of_birth,
            'first_name'=>$request->first_name,
            'last_name'=>$request->last_name,
            'phone'=>$request->phone,
            'email'=>$request->email,
            'second_dose_date'=>$request->second_dose_date,
            'third_dose_date'=>$request->third_dose_date,
            'office'=>$request->office,
            'category_id'=>$request->category_id,
        ];
        return view('step6', $params);
    }
    
    public function register(Request $request)
    {
        $municipal_code = $request->municipal_code;
        $coupon_code = $request->coupon_code;
            
        $patients = DB::table('patients')
            ->where('municipal_code', $request->municipal_code)
            ->where('coupon_code', $coupon_code)
            ->get();
                
        if (!$patients->isEmpty()) {
            return view('error', ['error'=>"市町村コードと接種券番号の組み合わせは既に登録済みです"]);
        }
  
        if (!preg_match('/^([0-9]{6})$/', $municipal_code)) {
            return view('error', ['error'=>"市町村コードが間違っています"]);
        }
        
        if (!preg_match('/^([0-9]{10})$/', $coupon_code)) {
            return view('error', ['error'=>"接種券番号が間違っています"]);
        }

        $office = empty($request->office) ? "" : "事業所｜".$request->office;

        $params = [
            'municipal_code' => $municipal_code,
            'coupon_code' => $coupon_code,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'phone'=>$request->phone,
            'email'=>$request->email,
            'category_id'=> $request->category_id,
            'password' => $request->date_of_birth,
            'second_dose_date'=> $request->second_dose_date,
            'third_dose_date'=> $request->third_dose_date,
            'comment'=>$office
        ];
        
        DB::table('patients')->insert($params);
        
        
        $patients = DB::table('patients')
            ->where('municipal_code', $municipal_code)
            ->where('coupon_code', $coupon_code)
            ->get();
        
        $patient_id = $patients->first()->patient_id;
        Session::put("patient_id", $patient_id);
        
        return redirect("/complete");
    }
 

    public function mypage()
    {
        $site = DB::table('site')->get()->first();

        // カレンダーの開始日をリセットする
        Session::put("start_date", date('Y-m-d'));

        $patient_id = Session::get('patient_id');
        $mode = Session::get('mode');

        $patient = DB::table('patients')->where('patient_id', $patient_id)->get()->first();
        
        $reservations = DB::table('reservations')
            ->join('patients', 'reservations.patient_id', '=', 'patients.patient_id')
            ->join('statuses', 'reservations.status_code', '=', 'statuses.status_code')
            ->join('frames', 'reservations.frame_id', '=', 'frames.frame_id')
            ->join('vaccines', 'frames.vaccine_id', '=', 'vaccines.vaccine_id')
            ->where('reservations.patient_id', $patient_id)
            ->where('reservations.status_code', '!=', 3)
            ->orderBy('vaccines.vaccine_id')
            ->orderBy('frames.start_at')
            ->get();
        
        // 2回目接種日が入力されているなら３回目接種から
        // それ以外は１回目接種から
        if (!empty($patient->third_dose_date)) {
            $start_dose = 4;
        } else if (!empty($patient->second_dose_date)) {
            $start_dose = 3;
        } else {
            $start_dose = 1;
        }

        foreach ($reservations as $reservation) {
            $reserved_dose = $reservation->vaccine_id%10;
            if ($reserved_dose >= $start_dose) {
                $start_dose = $reserved_dose + 1;
            }
        }

        $vaccine_list = [];

        for ($target_dose = $start_dose; $target_dose < 5; $target_dose++) {
       

            // 接種可能なワクチンのリストを作成
            $vaccines =  DB::table('vaccines')
                ->whereRaw("mod(vaccine_id,10) = $target_dose")
                ->get();

            $target_vaccines = [];

            $reservation_found = false;
            $prev_dose_avail = false;
            foreach ($vaccines as $vaccine) {
                $vaccine_id = $vaccine->vaccine_id;
                    
                $target_vaccines[floor($vaccine_id/10)] = $vaccine_id;

                if (!empty($this->getPrevDosageAt($patient_id, $vaccine_id))) {
                    $prev_dose_avail = true;
                }
            }

            foreach ($target_vaccines as $maker => $vaccine_id) {

                // 前回の接種日時を確認
                $prev_dosage = $this->getPrevDosageAt($patient_id, $vaccine_id);
                        
                $frames = DB::table('frames');
                $frames->where('category_id', $patient->category_id);
                $frames->where('vaccine_id', $vaccine_id);
                $frames->whereColumn('vaccine_count', '>', 'reservation_count');

                if (!empty($prev_dosage)) {
                    $dosage_interval = $this->getDosageInterval($vaccine_id);
                    if ($dosage_interval == 150) {
                        $frames->whereRaw("start_at >= '".$this->afterFiveMonth($prev_dosage)."'");
                    } elseif ($dosage_interval == 180) {
                        $frames->whereRaw("start_at >= '".$this->afterSixMonth($prev_dosage)."'");
                    } elseif ($dosage_interval == 210) {
                        $frames->whereRaw("start_at >= '".$this->afterSevenMonth($prev_dosage)."'");
                    } else {
                        $frames->whereRaw("start_at >= date_add('{$prev_dosage}',interval {$dosage_interval} day)");
                    }
                }
                if (($maker == 1 && $site->web_vaccine_14_today_reservation_limit > 0) || ($maker == 2 && $site->web_vaccine_24_today_reservation_limit > 0)) {
                    $frames->whereRaw('start_at > now()');
                } else {
                    $frames->whereRaw('start_at > DATE_ADD(CURRENT_DATE,interval 1 day)');
                }
                    
                // 枠が無い場合は予約不可
                if ($frames->count() == 0) {
                    unset($target_vaccines[$maker]);
                }
            }

            foreach ($target_vaccines as $maker => $vaccine_id) {
                $text = "";
                
                if (env('LOCATION', '') == 'HIGASHINAEBO') {
                    $text .= "コロナワクチン";
                } elseif ($maker == "1") {
                    $text .= "ファイザー";
                } elseif ($maker == "2") {
                    $text .= "モデルナ";
                }
                
                if ($target_dose == 1) {
                    $text  .= "１，２";
                } elseif ($target_dose == 2) {
                    $text  .= "２";
                } elseif ($target_dose == 3) {
                    $text  .= "３";
                } elseif ($target_dose == 4) {
                    $text  .= "４";
                }
                $text .= "回目を予約する";
                
                $skip = false;
                if ($target_dose == 4) {
                    // 3回目接種のみ予約制限をチェックする
                    $skip = false;
                    if ($patient->category_id == 1) {
                        if ($maker == 1  && ($site->web_vaccine_14_reservation_limit <= 0 && $site->web_vaccine_14_today_reservation_limit <= 0)) {
                            $skip = true;
                        } elseif ($maker == 2  && ($site->web_vaccine_24_reservation_limit <= 0 && $site->web_vaccine_24_today_reservation_limit <= 0)) {
                            $skip = true;
                        }
                    }
                }
                
                if (!$skip) {
                    $vaccine_list[] = ['vaccine_id'=>$vaccine_id,'text'=>$text];
                }
            }
        }

        //　キャンセルボタンを表示するか判断する
        $ccount = DB::table('reservations')
            ->join('patients', 'reservations.patient_id', '=', 'patients.patient_id')
            ->join('frames', 'reservations.frame_id', '=', 'frames.frame_id')
            ->join('vaccines', 'frames.vaccine_id', '=', 'vaccines.vaccine_id')
            ->where('reservations.patient_id', $patient_id)
            ->where('reservations.status_code', '!=', 3)    //取消済み
            ->where('reservations.status_code', '!=', 4)    //接種済み
            ->whereRaw('start_at > now()') // 過去予約取消禁止
            ->count();
    
            
        $cancellation_possible = ($ccount > 0?1:0);
         
        $vaccinations = DB::table('vaccinations')->where('patient_id', $patient_id)->orderBy('vaccine_id')->get();
         
        return view('mypage', [
               'reservations'=>$reservations,
               'vaccine_list'=>$vaccine_list,
               'cancellation_possible'=>$cancellation_possible,
               'vaccinations'=>$vaccinations,
               'patient'=>$patient
        ]);
    }
    
        

    
    public function complete()
    {
        return view('complete');
    }
    
    
    public function reserve($frame_id)
    {
        try {
            $result = DB::transaction(function () use ($frame_id) {
                $patient_id = Session::get('patient_id');
                $patient = DB::table('patients')->where('patient_id', $patient_id)->get()->first();
                $category_id = $patient->category_id;

                $site = DB::table('site')->lockForUpdate()->get()->first();

                $query = DB::table('frames');
                $query->where('frame_id', $frame_id);
                $query->whereRaw('start_at > now()');

                $frame = $query->lockForUpdate()->first();

                if ($frame == null) {
                    return 1;
                }
                
                $reservations = DB::table('reservations')
                    ->join('frames', 'reservations.frame_id', '=', 'frames.frame_id')
                    ->where('patient_id', $patient_id)
                    ->where('status_code', '!=', 3) // cancelled
                    ->where('frames.vaccine_id', $frame->vaccine_id)
                    ->get();
                
                if (!$reservations->isEmpty()) {
                    return 5; // 同じワクチンを予約済み
                }
                
                if ($frame->vaccine_count <= $frame->reservation_count) {
                    return 3;
                } // 枠が満員
                
                $todayReservation  = false;
                $dateTime = explode(" ", $frame->start_at);
                if ($dateTime[0] === date("Y-m-d")) {
                    $todayReservation = true;
                }
                 
                $vaccine_id = $frame->vaccine_id;

                $reservation_limit = 0;
                $today_reservation_limit =0;
                if (floor($vaccine_id/10) == 1) {
                    // ファイザー
                    $reservation_limit = $site->web_vaccine_14_reservation_limit;
                    $today_reservation_limit = $site->web_vaccine_14_today_reservation_limit;
                } elseif (floor($vaccine_id/10) == 2) {
                    // モデルナ
                    $reservation_limit = $site->web_vaccine_24_reservation_limit;
                    $today_reservation_limit = $site->web_vaccine_24_today_reservation_limit;
                }

                // WEB予約は受付制限をチェックする
                if ($category_id == 1) {
                    if ($todayReservation) {
                        if ($today_reservation_limit <= 0) {
                            return 3;
                        }
                    } elseif ($reservation_limit <= 0) {
                        return 2;
                    } // 新規予約の受付を停止
                }

                // 空きフレーム獲得, 枠の予約数+1
                DB::table('frames')->where('frame_id', $frame_id)->increment('reservation_count');

                // 4回目WEB予約時のみ、全体の予約可能件数を調整
                if ($category_id == 1 && $vaccine_id%10 == 4) {
                    if ($todayReservation) {
                        if (floor($vaccine_id/10) == 1) {
                            DB::table('site')->where('site_id', $site->site_id)->decrement('web_vaccine_14_today_reservation_limit');
                        } elseif (floor($vaccine_id/10) == 2) {
                            DB::table('site')->where('site_id', $site->site_id)->decrement('web_vaccine_24_today_reservation_limit');
                        }
                    } else {
                        if (floor($vaccine_id/10) == 1) {
                            DB::table('site')->where('site_id', $site->site_id)->decrement('web_vaccine_14_reservation_limit');
                        } elseif (floor($vaccine_id/10) == 2) {
                            DB::table('site')->where('site_id', $site->site_id)->decrement('web_vaccine_24_reservation_limit');
                        }
                    }
                }

                // 予約実施
                $params = [
                    'patient_id' => $patient_id,
                    'frame_id' => $frame_id,
                    'status_code' => 1,
                    'comment' => ''
                ];
                DB::table('reservations')->insert($params);

                
                // 1回目予約のみ２回目予約を自動設定する
                if ($vaccine_id % 10 == 1) {
                    $vaccine_id++;  // 2回目予約のワクチンID
                    
                    $lastReservationAt = DB::table('frames')->where('frame_id', $frame_id)->get()->first()->start_at;
                        
                    // 既に同じ予約が入っていないか確認
                    $reservations = DB::table('reservations')
                    ->join('frames', 'frames.frame_id', '=', 'reservations.frame_id')
                    ->where('patient_id', $patient_id)
                    ->where('frames.vaccine_id', $vaccine_id)
                    ->where('status_code', '!=', 3) // キャンセルを除く
                    ->get();
                    
                    if ($reservations->isEmpty()) {
                        // 予約が見つからない
                        $data = DB::table('frames');
                        
                        $data->whereRaw('start_at > DATE_ADD(CURRENT_DATE,interval 1 day)');
                                
                        // 接種間隔
                        $prevDosage = $lastReservationAt;
                        $dosageInterval = $this->getDosageInterval($vaccine_id);
                        $data->whereRaw("start_at >= date_add('{$prevDosage}',interval {$dosageInterval} day)");
                                
                        $data->where('vaccine_id', $vaccine_id);
                        $data->where('category_id', $category_id);
                        $data->whereColumn('vaccine_count', '>', 'reservation_count');
                        $data->orderBy('start_at');
                        $frames = $data->lockForUpdate()->get();
                                
                        if (!$frames->isEmpty()) {
                            $frame2 = $frames->first();
                            // 空きフレーム獲得, 枠の予約数+1
                            DB::table('frames')->where('frame_id', $frame2->frame_id)->increment('reservation_count');
                                    
                            $lastReservationAt = $frame2->start_at;
                                    
                            // 予約実施
                            $params = [
                                'patient_id' => $patient_id,
                                'frame_id' => $frame2->frame_id,
                                'status_code'=> 1,
                                'comment'=>'',
                            ];
                            DB::table('reservations')->insert($params);
                        }
                    }
                }
                
                return 0;
            });
        } catch (Throwable $e) {
            $result = 4;
            Log::debug('DBエラー:'.$e->getMessage());
        }

        if ($result == 0) {
            Session::flash('message', '予約に成功しました');
            return redirect("/mypage");
        }

        if ($result == 1) {
            Session::flash('error', '指定された枠が存在しません');
        } elseif ($result == 2) {
            Session::flash('error', '新規予約の受付を停止しています');
        } elseif ($result == 3) {
            Session::flash('error', '予約枠が一杯で予約できませんでした');
        } elseif ($result == 4) {
            Session::flash('error', 'データベースエラーにより予約ができませんでした。　しばらくたってから再度お試しください');
        } elseif ($result == 5) {
            Session::flash('error', '同じワクチンを既に予約しています');
        }
        return redirect()->back();
    }
    
    public function cancel()
    {
        $result = 0;
        try {
            $result = DB::transaction(function () {
                $patient_id = Session::get('patient_id');
                $patient = DB::table('patients')->where('patient_id', $patient_id)->get()->first();

                $query = DB::table('reservations');
                $query->join('frames', 'frames.frame_id', '=', 'reservations.frame_id');
                $query->where('patient_id', $patient_id);
                if (env('PREVENT_TODAY_CANCEL',false)) {
                    $query->whereRaw('start_at > DATE_ADD(CURRENT_DATE,interval 1 day)');
                } else {
                    $query->whereRaw('start_at > now()'); // 3回目接種に伴い、過去予約キャンセルの廃止
                } 
                $query->where('status_code', '!=', 3); //　キャンセル済み
                $query->where('status_code', '!=', 4); // 接種済み
                $query->lockForUpdate();
                $cancellations = $query->get();
            
 
                if ($cancellations->isEmpty()) {
                    return 2;
                }
                $firstTime = "";
                
                $pfizerCancel = false;
                $modernaCancel = false;

                foreach ($cancellations as $cancel) {
                    // 予約枠の予約数を一つ減らす
                    if ($cancel->vaccine_id % 10 == 3 && $cancel->category_id == 1) {
                        if (floor($cancel->vaccine_id/10) == 1) {
                            $pfizerCancel = true;
                        } elseif (floor($cancel->vaccine_id/10) == 2) {
                            $modernaCancel = true;
                        }

                        $firstTime = $cancel->start_at;
                    }
                   
                    DB::table('frames')->where('frame_id', $cancel->frame_id)->lockForUpdate()->decrement('reservation_count');
                    DB::table('reservations')->where('reservation_id', $cancel->reservation_id)->update(['status_code'=>3,'comment'=>'ユーザ操作による取消']);
                }
    
                // 予約枠の増加は１回目をキャンセルした場合のみ
                if ($pfizerCancel || $modernaCancel) {
                    $dateTime = explode(" ", $firstTime);
                    if (env('INCREASE_ON_CANCEL', 1)) {
                        if ($dateTime[0] === date("Y-m-d")) {
                            // 当日キャンセル
                            if ($pfizerCancel) {
                                DB::table('site')->lockForUpdate()->increment('web_vaccine_14_today_reservation_limit');
                            } elseif ($modernaCancel) {
                                DB::table('site')->lockForUpdate()->increment('web_vaccine_24_today_reservation_limit');
                            }
                        } else {
                            if ($pfizerCancel) {
                                DB::table('site')->lockForUpdate()->increment('web_vaccine_14_reservation_limit');
                            } elseif ($modernaCancel) {
                                DB::table('site')->lockForUpdate()->increment('web_vaccine_24_reservation_limit');
                            }
                        }
                    }
                }
                return 1;
            });
        } catch (Throwable $e) {
            $result = 3;
        }
        
        if ($result == 2) {
            Session::flash('error', '取消可能な予約がありません');
        } elseif ($result == 3) {
            Session::flash('error', 'データベースエラーにより予約取消ができませんでした。　しばらくたってから再度お試しください');
        } else {
            Session::flash('message', '全ての予約は取り消されました');
        }
        return redirect("/mypage");
    }
    
    public function calendarStart($date)
    {
        Session::put("start_date", $date);
    }
}
