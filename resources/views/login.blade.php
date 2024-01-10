@extends('layouts.template')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6 mt-5">
                <div class="card p-4" style="background-color: #607274; color: white; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                    <h2 class="text-center mb-4" style="text-shadow: 2px 2px 4px #000;">Login</h2>
                    
                    <form action="{{ route('auth.login') }}" method="POST">
                        @csrf
                        
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(Session::get('failed'))
                            <div class="alert alert-danger">{{ Session::get('failed') }}</div>
                        @endif

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="Masukan email anda ...">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Masukan password anda....">
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg btn-block" style="background-color: #2c3e50; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
