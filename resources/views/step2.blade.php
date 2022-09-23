@extends('base')

@section('title','ご利用に関して')

@section('css')
<link href="{{ asset('/css/style.css') }}" rel='stylesheet'>
@component('bootstrap_css')
@endcomponent
@endsection

@section('script')
@component('bootstrap_script')
@endcomponent



@section('body')


<style>

input[type="checkbox"]{
  transform: scale(2);
}

</style>

<div class="container">
<form id="interview-form" class="needs-validation" action="/step4" method="post" autocomplete="off">
@csrf
<div class="row my-5 justify-content-center">
	<div class="col-12 text-center">
		<span class="h3">ワクチン接種時の留意事項</span>
	</div>
</div>
<div class="row">
	<div class="col-12">
		<p>接種当日は必ず事前に検温をお願いいたします。37.5℃以上の場合は接種できません。（必要に応じて#7119へご相談ください）</p>
		<p>下記に該当する方は、当院でのワクチン接種をお断りいたします。救急体制の整った医療機関、市の集団接種会場などで接種をうけてください。
			<ul>
				<li>心臓病、腎臓病、肝臓病、血液疾患、血が止まりにくい病気、癌などの治療中で、接種可能か担当医の確認が取れていない場合や、注意が必要と言われている方</li>
				<li>喘息や気管支炎の治療中で、接種可能か担当医の確認が取れていない場合や、注意が必要と言われている方</li>
				<li>薬剤や食物、ワクチン接種で血圧低下や意識消失を伴うような強いアレルギー症状、アナフィラキシーになったことがある方</li>
			</ul>
		</p>
		<p>接種券、記入済み予診票及び本人確認書類（運転免許証、健康保険証など）必ずご用意ください。</p>
		<p>なるべく半袖、袖なし、それに羽織る服装でお越しいただきますようご協力をお願いいたします。腕まくりは注射の正確さを損ないます。</p>
		<p>万が一、アナフィラキシーによる処置が必要になった場合、ワンピースやつなぎなどの服装は、はさみで切る可能性があります。</p>
		<p>接種後は、15分間安静にして頂きます。</p>
		<p>副反応により、発熱した場合は、配置薬の解熱剤の対応となります。症状の軽快がみられない場合、札幌市の指定相談窓口（0120-306-154）に相談いただいたり、場合によって119にて救急要請となることもありえます。<p>
		<p>主な副反応の症状として、発熱（38.1％）、全身倦怠感（69％）、頭痛（54％）と報告されています。</p>
		<p>新型コロナワクチンに関する情報は、厚生労働省等が発表するものでご確認ください。</p>
	</div>
</div>
<div class="row justify-content-center my-4">
	<div class="col-12 text-center">
		<input class="form-check-input"  type="checkbox" id="agree-checkbox" autocomplete="off" required>
		<label class="form-check-label" for="to-contact-form-pc"><b>&nbsp;同意する</b></label>
	</div>
</div>
<div class="row justify-content-center mt-2">
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
<input type="hidden" name="first_name" value="{{$first_name}}">
<input type="hidden" name="last_name" value="{{$last_name}}">
</form>
</div>

<form action="{{$from}}" name="back_form" method="post" autocomplete="off" >
@csrf
	<input type="hidden" name="municipal_code" value="{{$municipal_code}}">
    <input type="hidden" name="coupon_code" value="{{$coupon_code}}">
    <input type="hidden" name="date_of_birth" value="{{$date_of_birth}}">	
    <input type="hidden" name="category_id" value="{{$category_id}}">
    <input type="hidden" name="office" value="{{$office}}">
	<input type="hidden" name="first_name" value="{{$first_name}}">
    <input type="hidden" name="last_name" value="{{$last_name}}">
</form>

@endsection



