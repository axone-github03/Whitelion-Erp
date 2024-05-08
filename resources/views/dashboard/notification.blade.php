
@foreach($data['notification'] as $nK=>$nV)



@php

$routeURL="";
if($nV->type==1 || $nV->type==2 || $nV->type==3 || $nV->type==4){
$routeURL=route('inquiry')."?status=0&inquiry_id=".$nV->inquiry_id;
}else{
 $routeURL='javascript: void(0);';
}


@endphp
       <a href="{{$routeURL}}" target="_blank" class="text-reset notification-item" notification-id="notification-id-{{$nV->id}}" >
                                        <div class="d-flex">
                                            <div class="user-notification-avatar-xs avatar-xs me-3">
                                                <span class="avatar-title bg-primary rounded-circle font-size-12">
                                                    {{$nV['A']}}{{$nV['B']}}
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-2" >{{$nV->title}}</h6>
                                                <div class="font-size-12 text-muted">
                                                    <p class="mb-1" >{!!$nV->description !!}</p>

                                           <div class="d-flex flex-wrap gap-1">

                                            <button type="button" class="btn btn-sm"><i class="mdi mdi-clock-outline"></i> <span >{{convertDateTime($nV->created_at)}}</span></button>

                                            @if($nV->is_read==1)

                                            <button id="btn-notification-read-{{$data['tab']}}-{{$nV->id}}" onclick="unreadNotification({{$nV->id}})" type="button" class="btn btn-notification-action btn-primary btn-sm waves-effect waves-light"><i class="mdi mdi-circle-outline user-notication-btm"></i><span> Mark as unread</span></button>

                                            @endif

                                             @if($nV->is_read==0)

                                            <button id="btn-notification-read-{{$data['tab']}}-{{$nV->id}}"  onclick="readNotification({{$nV->id}})" type="button" class="btn btn-notification-action btn-outline-primary btn-sm waves-effect waves-light"><i class="mdi mdi-circle-outline user-notication-btm" ></i><span> Mark as read</span></button>

                                            @endif

                                            @if($nV->is_favourite==0)

                                            <button id="btn-notification-favourite-{{$data['tab']}}-{{$nV->id}}"   onclick="favouriteNotification({{$nV->id}})" type="button" class="btn btn-notification-action btn-outline-primary btn-sm waves-effect waves-light"><i class="mdi mdi-star user-notication-btm"></i><span > Favourite</span></button>

                                            @endif

                                              @if($nV->is_favourite==1)

                                            <button  id="btn-notification-favourite-{{$data['tab']}}-{{$nV->id}}"  onclick="removeFromFavouriteNotification({{$nV->id}})" type="button" class="btn btn-notification-action btn-primary btn-sm waves-effect waves-light"><i class="mdi mdi-star user-notication-btm"></i><span> Favourite</span></button>

                                            @endif







                                        </div>

                                                </div>
                                            </div>
                                        </div>
                                    </a>
@endforeach
