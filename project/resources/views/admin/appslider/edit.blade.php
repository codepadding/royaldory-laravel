@extends('layouts.admin')

@section('content')

    <div class="content-area">
        <div class="mr-breadcrumb">
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="heading">{{ __('Edit Slider') }} <a class="add-btn" href="{{route('admin-sl-index')}}"><i
                                    class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h4>
                    <ul class="links">
                        <li>
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>
                        </li>
                        <li>
                            <a href="javascript:;">{{ __('Home Page Settings') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('app-sl-index') }}">{{ __('App Sliders') }}</a>
                        </li>
                        <li>
                            <a href="{{route('app-sl-edit',$data->id)}}">{{ __('Edit') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="add-product-content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="product-description">
                        <div class="body-area">
                            <div class="gocover"
                                 style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                            <form id="geniusform" action="{{route('app-sl-update',$data->id)}}" method="POST"
                                  enctype="multipart/form-data">
                                {{csrf_field()}}
                                @include('includes.admin.form-both')



                                {{-- Title Section --}}

                                <div class="panel panel-default slider-panel">
                                    <div class="panel-heading text-center"><h3>{{ __('Title') }}</h3></div>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <label class="control-label" for="title_text">{{ __('Text') }}*</label>

                                                <textarea class="form-control" name="title_text" id="title_text"
                                                          rows="5"
                                                          placeholder="{{ __('Enter Title Text') }}">{{$data->title_text}}</textarea>
                                            </div>
                                        </div>



                                    </div>
                                </div>

                                {{-- Title Section Ends--}}

                                {{-- Sub Title Section --}}



                                {{-- Sub Title Section Ends--}}
                                {{-- Details Section --}}



                                {{-- Title Section Ends--}}


                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="left-area">
                                            <h4 class="heading">{{ __('Current Featured Image') }} *</h4>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <div class="img-upload full-width-img">
                                            <div id="image-preview" class="img-preview"
                                                 style="background: url({{ $data->photo ? asset('assets/images/appsliders/'.$data->photo):asset('assets/images/noimage.png') }});">
                                                <label for="image-upload" class="img-label" id="image-label"><i
                                                            class="icofont-upload-alt"></i>{{ __('Upload Image') }}
                                                </label>
                                                <input type="file" name="photo" class="img-upload" id="image-upload">
                                            </div>
                                            <p class="text">{{ __('Prefered Size: (300x150) or Square Sized Image') }}</p>
                                        </div>

                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="left-area">
                                            <h4 class="heading">{{ __('Link') }} *</h4>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <input type="text" class="input-field" name="link" placeholder="Link"
                                               value="{{$data->link}}">
                                    </div>
                                </div>




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