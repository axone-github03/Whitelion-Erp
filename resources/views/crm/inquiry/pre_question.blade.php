@foreach($data['question'] as $keyQ =>$valueQ)
@if($valueQ->type==1)

                                         <div class="row row-more-detail" id="row-more_answer_{{$valueQ->id}}"  >
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="pre_inquiry_questions_{{$valueQ->id}}" class="form-label">{{$valueQ->question}}

                                                       </label>
                                                        <select id="pre_inquiry_questions_{{$valueQ->id}}" name="pre_inquiry_questions_{{$valueQ->id}}" class="form-select pre-select2-apply"   >

                                                        <option value="">Select Option</option>
                                                        @foreach($valueQ['options'] as $OptK=>$OptV)
                                                        <option value="{{$OptV->id}}">{{$OptV->option}} </option>
                                                        @endforeach
                                                            </select>

                                                    </div>
                                                </div>
                                            </div>
@endif
@if($valueQ->type==2)
    <div class="row row-more-detail" id="row-more_answer_{{$valueQ->id}}"  >
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="pre_inquiry_questions_{{$valueQ->id}}" class="form-label"> {{$valueQ->question}}</label>
                                                         <input class="form-control" type="file" value=""
                                                     id="pre_inquiry_questions_{{$valueQ->id}}" name="pre_inquiry_questions_{{$valueQ->id}}" >

                                                    </div>
                                                </div>
                                            </div>


@endif
@endforeach