@extends('layouts.load')

@section('content')

    <div class="content-area">

        <div class="add-product-content1">
            <div class="row">
                <div class="col-lg-12">
                    <div class="product-description">
                        <div class="body-area">
                            @include('includes.admin.form-error')
                            <form id="geniusformdata" action="{{route('admin-pn-update',$data->id)}}" method="POST"
                                  enctype="multipart/form-data">
                                {{csrf_field()}}


                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="left-area">
                                            <h4 class="heading">{{ __('Title') }} *</h4>
                                            <p class="sub-heading">{{ __('(In Any Language)') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <input type="text" class="input-field" name="title"
                                               placeholder="{{ __('Enter Title') }}" required=""
                                               value="{{$data->title}}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="left-area">
                                            <h4 class="heading">{{ __('Sub Title') }} </h4>
                                            <p class="sub-heading">{{ __('(In Any Language)') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <textarea class="input-field" name="sub_title"
                                                  placeholder="{{ __('Enter Sub Title') }}"
                                                 >{{$data->sub_title}} </textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="left-area">
                                            <h4 class="heading">{{ __('Message') }} *</h4>
                                            <p class="sub-heading">{{ __('(In Any Language)') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <textarea class="input-field" name="message"
                                                  placeholder="{{ __('Enter Message') }}"
                                                  required="">{{$data->message}}</textarea>
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="left-area">
                                            <h4 class="heading">{{ __('Current Icon') }} </h4>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <div class="img-upload">
                                            <div id="image-preview" class="img-preview"
                                                 style="background: url({{ $data->thumb ? asset('assets/images/notification/'.$data->thumb):asset('assets/images/noimage.png') }});">
                                                <label for="image-upload" class="img-label" id="image-label"><i
                                                            class="icofont-upload-alt"></i>{{ __('Upload Thumb') }}
                                                </label>
                                                <input type="file" name="thumb" class="img-upload" id="image-upload">
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="left-area">
                                            <h4 class="heading">{{ __('Set Image') }} </h4>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <div class="img-upload full-width-img">
                                            <div id="image-preview" class="img-preview"
                                                 style="background: url({{ $data->image ? asset('assets/images/notification/images/'.$data->image):asset('assets/images/noimage.png') }});">
                                                <label for="image-upload2" class="img-label" id="image-label"><i
                                                            class="icofont-upload-alt"></i>{{ __('Upload Image') }}
                                                </label>
                                                <input type="file" name="image" class="img-upload" id="image-upload2">
                                            </div>
                                            <p class="text">{{ __('Prefered height: 250 pixel') }}</p>
                                        </div>

                                    </div>
                                </div>


{{--                                <div class="{{ $data->is_featured == 0 ? "showbox":"" }}">--}}

{{--                                    <div class="row">--}}
{{--                                        <div class="col-lg-4">--}}
{{--                                            <div class="left-area">--}}
{{--                                                <h4 class="heading">{{ __('Current Featured Image') }}*</h4>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="col-lg-7">--}}
{{--                                            <div class="img-upload">--}}
{{--                                                <div id="image-preview" class="img-preview"--}}
{{--                                                     style="background: url({{ $data->image ? asset('assets/images/categories/'.$data->image):asset('assets/images/noimage.png') }});">--}}
{{--                                                    <label for="image-upload" class="img-label"><i--}}
{{--                                                                class="icofont-upload-alt"></i>{{ __('Upload Image') }}--}}
{{--                                                    </label>--}}
{{--                                                    <input type="file" name="image" class="img-upload">--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}

{{--                                    </div>--}}


{{--                                </div>--}}


                                <br>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="left-area">

                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <button class="addProductSubmit-btn" type="submit">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
