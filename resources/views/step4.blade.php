@extends('base')

@section('title','お名前入力')

@section('css')
<link href="{{ asset('/css/style.css') }}" rel='stylesheet'>
@component('bootstrap_css')
@endcomponent
@endsection

@section('script')
@component('bootstrap_script')
@endcomponent

<script>
(function() {
    'use strict';
    window.addEventListener('load',function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit',function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            },false);
        });
        document.getElementById('dose5').addEventListener('change', doseChanged);
        document.getElementById('dose4').addEventListener('change', doseChanged);
        document.getElementById('dose3').addEventListener('change', doseChanged);

        let target = null;
        @if (!empty($second_dose_date))
            target = document.getElementById('dose3');
        @elseif (!empty($third_dose_date))
            target = document.getElementById('dose4');
        @else
            target = document.getElementById('dose5');
        @endif
        target.checked = true;
        target.dispatchEvent(new Event('change'));

    },false);
})();

function doseChanged(e) {
    if (e.currentTarget.checked) {

        if (e.currentTarget.value == 5) {
            document.getElementById('fourth_dose_row').style.display="flex";
            document.getElementById('fourth_dose_date').required = true;

            document.getElementById('third_dose_row').style.display="none";
            document.getElementById('third_dose_date').required = false;
            
            document.getElementById('second_dose_row').style.display="none";
            document.getElementById('second_dose_date').required = false;

        } else if (e.currentTarget.value == 4) {

            document.getElementById('fourth_dose_row').style.display="none";
            document.getElementById('fourth_dose_date').required = false;


            document.getElementById('third_dose_row').style.display="flex";
            document.getElementById('third_dose_date').required = true;

            document.getElementById('second_dose_row').style.display="none";
            document.getElementById('second_dose_date').required = false;


        } else {

            document.getElementById('fourth_dose_row').style.display="none";
            document.getElementById('fourth_dose_date').required = false;

            document.getElementById('second_dose_row').style.display="flex";
            document.getElementById('second_dose_date').required = true;

            document.getElementById('third_dose_row').style.display="none";
            document.getElementById('third_dose_date').required = false;
        }
        document.getElementById('fourth_dose_date').value = "";
        document.getElementById('second_dose_date').value = "";
        document.getElementById('third_dose_date').value = "";
    }
}


</script>

<style>
    input[type="radio"]{
        transform: scale(2);
    }

    #second_dose_row, #third_dose_row, #fourth_dose_row {
        display:none;
        flex-direction: column;
    }

</style>

@endsection



@section('body')


<div class="container main-container">
<form class="needs-validation" action="/step5" method="post" autocomplete="off" novalidate>
@csrf
    <div class="row justify-content-center mb-5">
    <span class="h3">お名前入力</span>
    </div>
    <div class="form-row">
    <div class="form-group col-12">
        <label for="last_name">姓&nbsp;<span class="badge badge-danger">必須</span></label>
        <input class="form-control" type="text" name="last_name" id="last_name" value="{{$last_name}}" placeholder="豊生" required>
            <div class="invalid-feedback">
            苗字を入力してください
            </div>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-12">
           <label for="first_name">名&nbsp;<span class="badge badge-danger">必須</span></label>
            <input class="form-control" type="text" name="first_name" id="first_name" value="{{$first_name}}" placeholder="太郎" required>
            <div class="invalid-feedback">
            名前を入力してください
            </div>
        </div>
    </div>
    <div class="form-row"> 
        <div class="form-group col-12">
            <label for="first_name">今回の接種　</label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="dose" id="dose5" value="5">
                <label class="form-check-label" for="inlineRadio1">５回目</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="dose" id="dose4" value="4">
                <label class="form-check-label" for="inlineRadio1">４回目</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="dose" id="dose3" value="3">
                <label class="form-check-label" for="inlineRadio2">３回目</label>
            </div>
        </div> 
    </div>
    <div id="fourth_dose_row">
        <div class="row"> 
            <div class="form-group col-12">
                <label for="first_name">コロナワクチン４回目接種日&nbsp;<span class="badge badge-danger">必須</span></label>
                <input class="form-control" type="text" name="fourth_dose_date" id="fourth_dose_date" value="{{$fourth_dose_date}}" inputmode="numeric" pattern="\d{8}" placeholder="20210519" required>
                <span class="small">５回目接種券に記載の４回目接種日を半角西暦８桁で入力</span><br>
                <span class="small">例：４回目接種日(2022年07月22日)->20220722と入力</span>
                <div class="invalid-feedback">４回目接種日を半角西暦８桁で入力してください</div>
            </div>
        </div>
        <div class="row">
            <img src="{{ asset('/img/fourth_dose_date.png')}}" id="third-dose-image" style="width:100%; height:auto;">
        </div>
    </div>
    <div id="third_dose_row">
        <div class="form-row"> 
            <div class="form-group col-12">
                <label for="first_name">コロナワクチン３回目接種日&nbsp;<span class="badge badge-danger">必須</span></label>
                <input class="form-control" type="text" name="third_dose_date" id="third_dose_date" value="{{$third_dose_date}}" inputmode="numeric" pattern="\d{8}" placeholder="20210519" required>
                <span class="small">４回目接種券に記載の３回目接種日を半角西暦８桁で入力</span><br>
                <span class="small">例：３回目接種日(2021年05月19日)->20210519と入力</span>
                <div class="invalid-feedback">３回目接種日を半角西暦８桁で入力してください</div>
            </div>
        </div>
        <div class="form-row d-flex flex-column">
            <img src="{{ asset('/img/third_dose_date.png')}}" id="third-dose-image" style="width:100%; height:auto;">
        </div>
    </div>
    <div id="second_dose_row">
        <div class="form-row"> 
            <div class="form-group col-12">
                <label for="first_name">コロナワクチン２回目接種日&nbsp;<span class="badge badge-danger">必須</span></label>
                <input class="form-control" type="text" name="second_dose_date" id="second_dose_date" value="{{$second_dose_date}}" inputmode="numeric" pattern="\d{8}" placeholder="20210519">
                <span class="small">３回目接種券に記載の２回目接種日を半角西暦８桁で入力</span><br>
                <span class="small">例：２回目接種日(2021年05月19日)->20210519と入力</span>
                <div class="invalid-feedback">２回目接種日を半角西暦８桁で入力してください</div>
            </div>
        </div>
        <div class="form-row">
            <img src="{{ asset('/img/second_dose_date.png')}}" id="second-dose-image" style="width:100%; height:auto;">
        </div>
    </div>
    <div class="row justify-content-center mt-5">
        <div class="form-group col-6">
            <button class="btn btn-outline-primary fit-btn" id="back-btn" type="button" onclick="document.back_form.submit();">戻る</button>
        </div>
        <div class="form-group col-6">
            <button class="btn btn-success fit-btn" id="next-btn" type="submit">次へ</button>
        </div>
    </div>
    <input type="hidden" name="municipal_code" value="{{$municipal_code}}">
    <input type="hidden" name="coupon_code" value="{{$coupon_code}}">
    <input type="hidden" name="date_of_birth" value="{{$date_of_birth}}">	
    <input type="hidden" name="category_id" value="{{$category_id}}">
    <input type="hidden" name="office" value="{{$office}}">
</form>
</div>


<form action="/step2" name="back_form" method="post" autocomplete="off" >
@csrf
    <input type="hidden" name="first_name" value="{{$first_name}}">
    <input type="hidden" name="last_name" value="{{$last_name}}">
    <input type="hidden" name="municipal_code" value="{{$municipal_code}}">
    <input type="hidden" name="coupon_code" value="{{$coupon_code}}">
    <input type="hidden" name="date_of_birth" value="{{$date_of_birth}}">	
    <input type="hidden" name="category_id" value="{{$category_id}}">
    <input type="hidden" name="office" value="{{$office}}">
</form>


@endsection

