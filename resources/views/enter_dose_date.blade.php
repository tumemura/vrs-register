@extends('base')

@section('title','接種日入力')

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


    },false);
})();



</script>

<style>
    input[type="radio"]{
        transform: scale(2);
    }

</style>

@endsection



@section('body')

<div class="container">
    <div class="main" style="height:85%">
        <div class="row my-5 justify-content-center">
            <div class="col-auto">
                <span class="h3">接種日入力</span>
            </div>
        </div>
        <form class="needs-validation" action="/save_dose_date" method="post" autocomplete="off" novalidate>
            @csrf
            <div class="row justify-content-center mb-5">
                <div class="col">
                {{ ($vaccine_id % 10)-1 }} 回目接種を他院で接種された方は接種日を入力してください。{{ ($vaccine_id % 10)-1 }} 回目接種をされていない方は、戻るを押してください<br>
                    </div>
            </div>
            <div style="margin: 1em;"></div>
            @if ($vaccine_id % 10 == 4)
            <div class="row" id="third-dose-row"> 
                <div class="form-group col-12">
                    <label for="first_name">コロナワクチン３回目接種日&nbsp;<span class="badge badge-danger">必須</span></label>
                    <input class="form-control" type="text" name="third_dose_date" id="third_dose_date" value="" inputmode="numeric" pattern="\d{8}" placeholder="20210519" required>
                    <span class="small">４回目接種券に記載の３回目接種日を半角西暦８桁で入力</span><br>
                    <span class="small">例：３回目接種日(2021年05月19日)->20210519と入力</span>
                    <div class="invalid-feedback">３回目接種日を半角西暦８桁で入力してください</div>
                </div>
            </div>
            @else if ($vaccine_id %10 == 3)
            <div class="row" id="second-dose-row"> 
                <div class="form-group col-12">
                    <label for="first_name">コロナワクチン２回目接種日&nbsp;<span class="badge badge-danger">必須</span></label>
                    <input class="form-control" type="text" name="second_dose_date" id="second_dose_date" value="" inputmode="numeric" pattern="\d{8}" placeholder="20210519">
                    <span class="small">３回目接種券に記載の２回目接種日を半角西暦８桁で入力</span><br>
                    <span class="small">例：２回目接種日(2021年05月19日)->20210519と入力</span>
                    <div class="invalid-feedback">２回目接種日を半角西暦８桁で入力してください</div>
                </div>
            </div>
            @endif
            <div class="form-row">
                <img src="{{ asset('/img/third_dose_date.png')}}" id="third-dose-image" style="width:100%; height:auto; display:block">
                <img src="{{ asset('/img/second_dose_date.png')}}" id="second-dose-image" style="width:100%; height:auto; display:none">
            </div>
            <div class="row justify-content-center mt-5">
                <div class="form-group col-6">
                    <button class="btn btn-outline-primary fit-btn" id="back-btn" type="button" onclick="location.href='/mypage'">戻る</button>
                </div>
                <div class="form-group col-6">
                    <button class="btn btn-success fit-btn" id="next-btn" type="submit">次へ</button>
                </div>
            </div>
            <input type="hidden" name="vaccine_id" value="{{$vaccine_id}}">
        </form>
    </div>
</div>

@endsection

