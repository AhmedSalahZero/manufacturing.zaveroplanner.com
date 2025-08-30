@extends('layouts.app')

@section('content')


    <div class="container text-center">
        <div class="row"  >
            <div class=" col-md-12">
            <div class="card-header text-center" style="color: black"> <h3>{{ __('USERS PROJECTS') }}</h3> </div>
                    <table class="dashboardTable tableItem" border="3" style="color: black;text-align: center ;white-space: nowrap; overflow: scroll;">

                        <thead>
                            <tr>
                                <th style="background-color: rgb(155, 174, 192)" width="25%">User Name  </th>
                                <th style="background-color: rgb(155, 174, 192)"> Joining Date </th>
                                <th style="background-color: rgb(155, 174, 192)"> Last Login Date </th>
                                <th style="background-color: rgb(155, 174, 192)">User E-mail  </th>
                                <th style="background-color: rgb(155, 174, 192)">Mobile </th>
                                <th style="background-color: rgb(155, 174, 192)">Number Of Projects  </th>
                                <th style="background-color: rgb(155, 174, 192)">Number Of Completed Projects  </th>
                            </tr>

                        </thead>
                        <tbody>
                            @foreach ($users as $user_data)
                                <tr>
                                    <td style="background-color: white"> {{ $user_data->name }} {{ $user_data->last_name }}   </td>
                                    <td style="background-color: white"> [ {{date('d-M-Y',strtotime($user_data->created_at))}} ]</td>
                                    <td style="background-color: white"> [ {{(isset($user_data->last_login)) ? date('d-M-Y',strtotime($user_data->last_login)) : "-" }} ]</td>
                                    <td style="background-color: white"> {{ $user_data->email }}  </td>
                                    <td style="background-color: white"> {{ $user_data->mobile ?? '-' }}  </td>
                                    <td style="background-color: white"> {{ count($user_data->projects) }}  </td>
                                    <?php
                                    $count =0;
                                        foreach ($user_data->projects as  $project) {
                                            (!isset($project->expense)) ?: $count++ ;
                                        }
                                    ?>
                                    <td style="background-color: white"> {{ $count }}  </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
         </div>
        {{-- </div> --}}
    </div>
@endsection
