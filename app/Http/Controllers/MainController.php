<?php

namespace App\Http\Controllers;

use Throwable;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

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
        $vaccines =  DB::table('vaccines')
        ->orderBy('vaccine_id', 'asc')
        ->get();

        $vaccine_list = [];

        foreach($vaccines as $vaccine) {
            $firstReservationDate = "";

            $firstFrame = DB::table('frames')
                ->whereRaw('start_at > DATE_ADD(CURRENT_DATE,interval 1 day)')
                ->where('vaccine_id', $vaccine->vaccine_id)
                ->where('category_id', 1)
                ->whereColumn('vaccine_count', '>', 'reservation_count')
                ->orderBy('start_at')
                ->first();

            $frames = \DB::table('frames');
            $frames->selectRaw("sum(vaccine_count) as total, sum(reservation_count) as used");
            $frames->where('category_id',1);
            $frames->where('vaccine_id',$vaccine->vaccine_id);
            $frames->whereRaw('start_at > DATE_ADD(CURRENT_DATE,interval 1 day)');
            $summary = $frames->get()->first();
            $available = empty($summary->total)? 0 :$summary->total - $summary->used;

            if (!empty($summary->total)) {

                if ($firstFrame) {
                    $firstReservationDate = date("Y/m/d", strtotime($firstFrame->start_at));
                }
                $vaccine_list[$vaccine->vaccine_id]['name'] = $vaccine->vaccine_name;
                $vaccine_list[$vaccine->vaccine_id]['available'] = $available;
                $vaccine_list[$vaccine->vaccine_id]['first_reservation_date'] = $firstReservationDate;
            }
        }

        $item = DB::table('site')->get()->first();
        
        return view('status', [
            'vaccine_list'=>$vaccine_list,
            'small'=>$small
        ]);
    }


    
    private function getDosageInterval($vaccine_id)
    {
        $vaccine = DB::table('vaccines')
        ->where('vaccine_id', $vaccine_id)
        ->first();
        return $vaccine->dosage_interval;
    }
    
  

    // ????????????????????????????????????
    private function getPrevDosageAt($patient_id, $vaccine_id)
    {
        $patients = DB::table('patients')
        ->where('patient_id', $patient_id)
        ->first();
        
        // 2???????????????????????????????????????????????????
        if ($vaccine_id %10 == 9) { 
            $prevDoseDate =  "";   
            if (!empty($patients->second_dose_date)) {
                $prevDoseDate =  explode(" ",$patients->second_dose_date)[0];
            }
            if (!empty($patients->third_dose_date)) {
                $prevDoseDate =  explode(" ",$patients->third_dose_date)[0];
            }
    
            $reservation = DB::table('reservations')
                ->join('frames', 'reservations.frame_id', '=', 'frames.frame_id')
                ->where('patient_id', $patient_id)
                ->where('status_code', '!=', 3)
                ->orderBy('frames.start_at','desc')
                ->first();

            if ($reservation) {
                $dateTime = explode(" ", $reservation->start_at);
                if (empty($prevDoseDate)) {
                    $prevDoseDate = $dateTime[0];
                } else {
                    $oldDate = new Carbon($prevDoseDate);
                    $newDate = new Carbon($dateTime[0]);
                    if ($newDate->gt($oldDate)) {
                        $prevDoseDate = $dateTime[0];
                    }
                }
            }

            if (!empty($prevDoseDate))
                return $prevDoseDate." 00:00:00";

            return $prevDoseDate;
        }

        if (($vaccine_id -1)%10 == 2) {
            // 3?????????????????????????????????????????????????????????????????????????????????
            $patients = DB::table('patients')
                ->where('patient_id', $patient_id)
                ->first();

            if (!empty($patients->second_dose_date)) {
                return $patients->second_dose_date;
            }
        } else if (($vaccine_id -1)%10 == 3) {
            // ????????????????????????????????????????????????????????????????????????????????????
            $patients = DB::table('patients')
                ->where('patient_id', $patient_id)
                ->first();

            if (!empty($patients->third_dose_date)) {
                return $patients->third_dose_date;
            }
        }

        if ($vaccine_id %10 > 1) {
            foreach ([10,20,30] as $vaccine) {
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

    public function afterThreeMonth($date)
    {
        $current = new DateTime($date);
        $item = DB::select(DB::raw("select date_add('{$date}',interval 3 month) as three_month"));
        $next = new DateTime($item[0]->three_month);

        if ($current->format('d') != $next->format('d')) {
            $next->modify('+1 day');
        }
        return $next->format("Y-m-d H:i:s");
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
     
        // ??????????????????????????????
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
                    Session::flash('error', '????????????????????????????????????????????????????????????');
                    return redirect("/mypage");
                } else if ($vaccine_id % 10 == 3) {
                    return redirect("/enter_dose_date/".$vaccine_id);
                } else {
                    return redirect("/enter_dose_date/".$vaccine_id);
                }
            }
        } else {
            $dosage_interval = $this->getDosageInterval($vaccine_id);
            if ($dosage_interval == 90) {
                $frames->whereRaw("start_at >= '".$this->afterThreeMonth($prev_dosage)."'");
            } elseif ($dosage_interval == 150) {
                $frames->whereRaw("start_at >= '".$this->afterFiveMonth($prev_dosage)."'");
            } elseif ($dosage_interval == 180) {
                $frames->whereRaw("start_at >= '".$this->afterSixMonth($prev_dosage)."'");
            } elseif ($dosage_interval == 210) {
                $frames->whereRaw("start_at >= '".$this->afterSevenMonth($prev_dosage)."'");
            } else {
                $frames->whereRaw("start_at >= date_add('{$prev_dosage}',interval {$dosage_interval} day)");
            }
            $frames->whereRaw('start_at > DATE_ADD(CURRENT_DATE,interval 1 day)');
            
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
            // ??????????????????
            $patient = $items->first();

            if ($patient->category_id != 1) {
                // WEB?????????WEB?????????????????????????????????WEB???????????????????????????
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
            // ????????????????????????
            return redirect()->back()->withInput()->withErrors('??????????????????????????????????????????????????????');
        }
        
        $site = DB::table('site')->get()->first();
        
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

        // ????????????
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
            // ??????????????? + ??????????????????????????????
            $patient = $items->first();

            if ($patient->category_id != 3) {
                // ?????????????????????????????????????????????????????????????????????????????????????????????????????????
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
            return redirect()->back()->withInput()->with('error', '??????????????????????????????????????????????????????');
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
        $query->where('category_id', $patient->category_id); // ?????????WEB)
        $query->where('vaccine_id', $vaccine_id);
        $query->whereColumn('vaccine_count', '>', 'reservation_count');

        $site = DB::table('site')->get()->first();

        $query->whereRaw('start_at > DATE_ADD(CURRENT_DATE,interval 1 day)');    
        $query->orderBy('start_at');
        $frames = $query->get();
    
        return view('frame', ['date'=>$date, 'frames'=>$frames]);
    }
    
    public function step1(Request $request)
    {
        // ????????????
        $params = [
            'municipal_code' => $request->municipal_code,
            'coupon_code' => $request->coupon_code,
            'date_of_birth' => $request->date_of_birth
        ];
        return view('step1', $params);
    }

    public function step1s(Request $request)
    {
        // ??????????????????
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
            // ????????????

            if ($request->first_name == "") {
                Session::flash('error', '?????????(???)????????????????????????');
                return back($status_code)->withInput();
            } elseif ($request->last_name == "") {
                Session::flash('error', '?????????(???)????????????????????????');
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
            // ??????????????????
            $patients = DB::table('patients')
                ->where('phone', $request->phone)
                ->where('date_of_birth', $request->date_of_birth)
                ->get();
        } else {
            Session::flash('error', '?????????????????????????????????????????????????????????????????????');
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
            Session::flash('error', '???????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????');
            return back($status_code)->withInput();
        }


        $comment = $patient->comment;
        if (!empty($comment)) {
            $comment .= ",";
        }
        $comment .= "????????????".$request->office;

        // ???????????????????????????????????????????????????
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
            // ????????????

            if ($request->first_name == "") {
                Session::flash('error', '?????????(???)????????????????????????');
                return back($status_code)->withInput();
            } elseif ($request->last_name == "") {
                Session::flash('error', '?????????(???)????????????????????????');
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
            // ??????????????????
            $patients = DB::table('patients')
                ->where('phone', $request->phone)
                ->where('date_of_birth', $request->date_of_birth)
                ->get();
        } else {
            Session::flash('error', '?????????????????????????????????????????????????????????????????????');
            return back($status_code)->withInput();
        }


        if ($patients->isEmpty()) {
            return redirect('/step1n');
        }

        $patient = $patients->first();

        if (strlen($patient->coupon_code) == 10) {
            Session::flash('error', '???????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????');
            return back($status_code)->withInput();
        }

        // ???????????????????????????????????????????????????
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
            return view('error', ['error'=>"?????????????????????????????????????????????????????????????????????????????????"]);
        }
  
        if (!preg_match('/^([0-9]{6})$/', $municipal_code)) {
            return view('error', ['error'=>"??????????????????????????????????????????"]);
        }
        
        if (!preg_match('/^([0-9]{10})$/', $coupon_code)) {
            return view('error', ['error'=>"???????????????????????????????????????"]);
        }

        $office = empty($request->office) ? "" : "????????????".$request->office;

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

        // ????????????????????????????????????????????????
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
        
        // 2??????????????????????????????????????????????????????????????????
        // ????????????????????????????????????
        if (!empty($patient->third_dose_date)) {
            $start_dose = 4;
        } else if (!empty($patient->second_dose_date)) {
            $start_dose = 3;
        } else {
            $start_dose = 1;
        }

        foreach ($reservations as $reservation) {
            $reserved_dose = $reservation->vaccine_id%10;
            if ($reserved_dose == 9) {
                $start_dose = 9;
                break;
            }

            if ($reserved_dose >= $start_dose) {
                $start_dose = $reserved_dose + 1;
            }
        }

        $vaccine_list = [];

        if ($start_dose != 9) {


            for ($target_dose = $start_dose; $target_dose < 10; $target_dose++) {


                // ????????????????????????1???2?????????????????????????????????
                if ($start_dose < 3 && $target_dose >= 8)
                    break;


                // ????????????????????????????????????????????????
                $vaccines =  DB::table('vaccines')
                    ->whereRaw("mod(vaccine_id,10) = $target_dose")
                    ->get();

                $target_vaccines = [];

            
                foreach ($vaccines as $vaccine) {

                    $vaccine_id = $vaccine->vaccine_id;
                        
                    $target_vaccines[floor($vaccine_id/10)] = $vaccine_id;
                }

                foreach ($target_vaccines as $maker => $vaccine_id) {

                    // ??????????????????????????????
                    $prev_dosage = $this->getPrevDosageAt($patient_id, $vaccine_id);
                            
                    $frames = DB::table('frames');
                    $frames->where('category_id', $patient->category_id);
                    $frames->where('vaccine_id', $vaccine_id);
                    $frames->whereColumn('vaccine_count', '>', 'reservation_count');

                    if (!empty($prev_dosage)) {
                        $dosage_interval = $this->getDosageInterval($vaccine_id);
                        if ($dosage_interval == 90) {
                            $frames->whereRaw("start_at >= '".$this->afterThreeMonth($prev_dosage)."'");
                        } elseif ($dosage_interval == 150) {
                            $frames->whereRaw("start_at >= '".$this->afterFiveMonth($prev_dosage)."'");
                        } elseif ($dosage_interval == 180) {
                            $frames->whereRaw("start_at >= '".$this->afterSixMonth($prev_dosage)."'");
                        } elseif ($dosage_interval == 210) {
                            $frames->whereRaw("start_at >= '".$this->afterSevenMonth($prev_dosage)."'");
                        } else {
                            $frames->whereRaw("start_at >= date_add('{$prev_dosage}',interval {$dosage_interval} day)");
                        }
                    }

                    $frames->whereRaw('start_at > DATE_ADD(CURRENT_DATE,interval 1 day)');
                        
                    // ?????????????????????????????????
                    if ($frames->count() == 0) {
                        unset($target_vaccines[$maker]);
                    }
                }

                foreach ($target_vaccines as $maker => $vaccine_id) {
                    $text = "";
                    
                    if ($maker == "4" || $maker == "6") {
                        $text .= "??????????????????????????????????????????";
                    } elseif ($maker == "5") {
                        $text .= "???????????????????????????????????????";
                    } else {

                        if ($maker == "1") {
                            $text .= "???????????????";
                        } elseif ($maker == "2") {
                            $text .= "????????????";
                        } elseif ($maker == "3") {
                            $text .= "??????????????????";
                        } 
                    
                        if ($target_dose == 1) {
                            $text  .= "?????????";
                        } elseif ($target_dose == 2) {
                            $text  .= "???";
                        } elseif ($target_dose == 3) {
                            $text  .= "???";
                        } elseif ($target_dose == 4) {
                            $text  .= "???";
                        }
                        $text .= "??????";
                    }

                    $text .="???????????????";
                                    
                    $vaccine_list[] = ['vaccine_id'=>$vaccine_id,'text'=>$text];

                }
            }

        }

        //?????????????????????????????????????????????????????????
        $ccount = DB::table('reservations')
            ->join('patients', 'reservations.patient_id', '=', 'patients.patient_id')
            ->join('frames', 'reservations.frame_id', '=', 'frames.frame_id')
            ->join('vaccines', 'frames.vaccine_id', '=', 'vaccines.vaccine_id')
            ->where('reservations.patient_id', $patient_id)
            ->where('reservations.status_code', '!=', 3)    //????????????
            ->where('reservations.status_code', '!=', 4)    //????????????
            ->whereRaw('start_at > now()') // ????????????????????????
            ->count();
    
            
        $cancellation_possible = ($ccount > 0?1:0);
         
        $vaccinations = DB::table('vaccinations')
            ->join('vaccines', 'vaccinations.vaccine_id', '=', 'vaccines.vaccine_id')
            ->where('patient_id', $patient_id)->orderBy('vaccinations.vaccine_id')->get();
         
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
                    return 5; // ?????????????????????????????????
                }
                
                if ($frame->vaccine_count <= $frame->reservation_count) {
                    return 3;
                } // ????????????
                
                $todayReservation  = false;
                $dateTime = explode(" ", $frame->start_at);
                if ($dateTime[0] === date("Y-m-d")) {
                    $todayReservation = true;
                }
                 
                $vaccine_id = $frame->vaccine_id;


                // ????????????????????????, ???????????????+1
                DB::table('frames')->where('frame_id', $frame_id)->increment('reservation_count');

   
                // ????????????
                $params = [
                    'patient_id' => $patient_id,
                    'frame_id' => $frame_id,
                    'status_code' => 1,
                    'comment' => ''
                ];
                DB::table('reservations')->insert($params);

                
                // 1??????????????????????????????????????????????????????
                if ($vaccine_id % 10 == 1) {
                    $vaccine_id++;  // 2???????????????????????????ID
                    
                    $lastReservationAt = DB::table('frames')->where('frame_id', $frame_id)->get()->first()->start_at;
                        
                    // ????????????????????????????????????????????????
                    $reservations = DB::table('reservations')
                    ->join('frames', 'frames.frame_id', '=', 'reservations.frame_id')
                    ->where('patient_id', $patient_id)
                    ->where('frames.vaccine_id', $vaccine_id)
                    ->where('status_code', '!=', 3) // ????????????????????????
                    ->get();
                    
                    if ($reservations->isEmpty()) {
                        // ???????????????????????????
                        $data = DB::table('frames');
                        
                        $data->whereRaw('start_at > DATE_ADD(CURRENT_DATE,interval 1 day)');
                                
                        // ????????????
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
                            // ????????????????????????, ???????????????+1
                            DB::table('frames')->where('frame_id', $frame2->frame_id)->increment('reservation_count');
                                    
                            $lastReservationAt = $frame2->start_at;
                                    
                            // ????????????
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
            Log::debug('DB?????????:'.$e->getMessage());
        }

        if ($result == 0) {
            Session::flash('message', '???????????????????????????');
            return redirect("/mypage");
        }

        if ($result == 1) {
            Session::flash('error', '???????????????????????????????????????');
        } elseif ($result == 2) {
            Session::flash('error', '?????????????????????????????????????????????');
        } elseif ($result == 3) {
            Session::flash('error', '???????????????????????????????????????????????????');
        } elseif ($result == 4) {
            Session::flash('error', '?????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????');
        } elseif ($result == 5) {
            Session::flash('error', '????????????????????????????????????????????????');
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
                    $query->whereRaw('start_at > now()'); // 3????????????????????????????????????????????????????????????
                } 
                $query->where('status_code', '!=', 3); //????????????????????????
                $query->where('status_code', '!=', 4); // ????????????
                $query->lockForUpdate();
                $cancellations = $query->get();
            
                if ($cancellations->isEmpty()) {
                    return 2;
                }

                foreach ($cancellations as $cancel) {
                    DB::table('frames')->where('frame_id', $cancel->frame_id)->lockForUpdate()->decrement('reservation_count');
                    DB::table('reservations')->where('reservation_id', $cancel->reservation_id)->update(['status_code'=>3,'comment'=>'??????????????????????????????']);
                }
    

                return 1;
            });
        } catch (Throwable $e) {
            $result = 3;
        }
        
        if ($result == 2) {
            Session::flash('error', '???????????????????????????????????????');
        } elseif ($result == 3) {
            Session::flash('error', '???????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????');
        } else {
            Session::flash('message', '??????????????????????????????????????????');
        }
        return redirect("/mypage");
    }
    
    public function calendarStart($date)
    {
        Session::put("start_date", $date);
    }
}
