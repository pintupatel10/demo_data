<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="Username">Full Name <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Full Name']) !!}
        @if ($errors->has('name'))
            <span class="help-block">
                <strong>{{ $errors->first('name') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="email">Email<span class="text-red">*</span></label>

    <div class="col-sm-8">
        {!! Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'email']) !!}
        @if ($errors->has('email'))
            <span class="help-block">
                <strong>{{ $errors->first('email') }}</strong>
            </span>
        @endif
    </div>
</div>



<div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="inputPassword3">Password @if (!isset($user)) <span class="text-red">*</span> @endif</label>
    <div class="col-sm-8">
        <input type="password" placeholder="Password" id="password" name="password" class="form-control" >
        @if ($errors->has('password'))
            <span class="help-block">
                <strong>{{ $errors->first('password') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="inputPassword3">Confirm Password @if (!isset($user)) <span class="text-red">*</span> @endif</label>

    <div class="col-sm-8">
        <input type="password" placeholder="Confirm password" id="password-confirm" name="password_confirmation" class="form-control" >
        @if ($errors->has('password_confirmation'))
            <span class="help-block">
             <strong>{{ $errors->first('password_confirmation') }}</strong>
            </span>
        @endif
    </div>
</div>

@if($role=="local")
<div class="form-group{{ $errors->has('location') ? ' has-error' : '' }}">
<label class="col-sm-2 control-label" for="location">Location<span class="text-red">*</span></label>

<div class="col-sm-10">
{!! Form::select('location', \App\User::$location, null, ['class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

@if ($errors->has('location'))
<span class="help-block">
<strong>{{ $errors->first('location') }}</strong>
</span>
@endif
</div>
</div>

@endif

{{--<div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">--}}
    {{--<label class="col-sm-2 control-label" for="role">Status <span class="text-red">*</span></label>--}}

    {{--<div class="col-sm-10">--}}

        {{--@foreach (\App\User::$status as $key => $value)--}}
        {{--<label>--}}
            {{--{!! Form::radio('status', $key, ($key=='active') ? true:false, ['class' => 'flat-red']) !!} <span style="margin-right: 10px">{{ $value }}</span>--}}
        {{--</label>--}}
        {{--@endforeach--}}

        {{--@if ($errors->has('status'))--}}
            {{--<span class="help-block">--}}
             {{--<strong>{{ $errors->first('status') }}</strong>--}}
            {{--</span>--}}
        {{--@endif--}}
    {{--</div>--}}
{{--</div>--}}