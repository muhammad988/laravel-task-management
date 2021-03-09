@extends('layout')

@section('main-content')
    <div>
        <div class="float-start">
            <h4 class="pb-3">My Tasks</h4>
        </div>
        <div class="float-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#task">Create Task</button>
        </div>
        <div class="clearfix"></div>
    </div>
    <p id="msg"></p>

    <div id="list">
        @foreach ($tasks as $task)
            <div class="card mt-4" data-order="{{$task->order}}" data-id="{{$task->id}}">
                <h5 class="card-header">
                    @if (!$task->completed)
                        {{ $task->title }}
                    @else
                        <del>{{ $task->title }}</del>
                    @endif

                    <span class="badge rounded-pill bg-warning text-dark">
                    {{ $task->created_at->diffForHumans() }}
                </span>
                </h5>
                <div class="card-body">
                    <div class="card-text">
                        <div class="float-start">
                            @if (!$task->completed)
                                {{ $task->description }}
                            @else
                                <del>{{ $task->description }}</del>
                            @endif
                            <br>

                            @if ($task->completed)
                                <span class="badge rounded-pill bg-success text-white">
                                completed
                            </span>
                            @endif

                            @if($task->updated_at!=$task->created_at)
                                <small>Last Updated - {{ $task->updated_at->diffForHumans() }} </small>
                            @endif
                        </div>
                        <div class="float-end">
                            <a href="{{ route('task.edit', $task->id) }}" class="button btn btn-success">
                                <i class="button fa fa-edit"></i>
                            </a>
                            <button  onclick="destroy({{$task->id}})" class="button btn btn-danger">
                                <i class="fa fa-trash button"></i>
                            </button>

                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="modal fade" id="task" tabindex="-1" aria-labelledby="taskLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskLabel">New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="needs-validation" method="POST" id="task-form" action="{{ route('task.store') }}" novalidate>
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3 ">
                            <div class="col-md-12">
                                <label for="validationCustom01" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" value="" name="title" required>

                            </div>
                            <div class="col-md-12">
                                <label for="validationCustom02" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" required></textarea>

                            </div>
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input"  value="1" name="completed" type="checkbox" id="completed">
                                    <label class="form-check-label"  for="completed">Completed</label>
                                </div>

                            </div>


                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary" type="submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>

        $("#task-form").validate({
            invalidHandler: function (event, validator) {
                $("#task-form").addClass('was-validated')
            },
            submitHandler: function () {
                let form = $("#task-form");

                let url = form.attr('action');
                $.ajax({
                    type: "POST",
                    url: url,
                    data: form.serialize(), // serializes the form's elements.
                    success: function (data) {
                        location.reload();
                    }
                });
            }
        });

        function destroy(id){
            var r = confirm("Are you sure ?");
            if (r == true) {
                $.ajax({
                    type: "POST",
                    url: `task/${id}`,
                    data: {'_method':'DELETE'},

                    success: function (data) {
                        $("#list").find("[data-id='" + id + "']").remove();
                    },
                    error: function(data) {
                        alert(data.responseJSON.message);
                    }
                });
            } else {
              return false;
            }

        }



    </script>
    <script>
        $(function () {
            // $("#list>div.card").draggable( "option", "cancel");

            var srcElement, dstElement;
            var srcIndex, dstIndex;

            $("#list>div.card").dragdrop({
                makeClone: true,
                sourceHide: true,
                dragClass: "shadow",
                canDrag: function ($src, event) {
                     if (!$(event.target).hasClass('button')){
                         srcElement = $src;
                         srcIndex = $src.index();
                         dstIndex = srcIndex;
                         return $src;
                     }

                },
                canDrop: function ($dst) {

                    if ($dst.is("div.card")) {
                        // console.log($dst);
                        // console.log($dst.dataset);

                        dstIndex = $dst.index();
                        dstElement = $dst;
                        if (srcIndex < dstIndex)
                            srcElement.insertAfter($dst);
                        else
                            srcElement.insertBefore($dst);
                    }
                    return true;
                },
                didDrop: function ($src) {
                    // Must have empty function in order to NOT move anything.
                    // Everything that needs to be done has been done in canDrop.
                    if (srcIndex != dstIndex) {
                        $.ajax({
                            type: "POST",
                            url: `{{route('order')}}`,
                            data: {'dstOrder':dstElement[0].dataset.order,'srcOrder':$src[0].dataset.order,'id':$src[0].dataset.id},
                            success: function (data) {
                               let $element=$('#list').children('div');
                                for (let i = 0; i < $element.length; i++) {
                                    $element[i].dataset.order=data.result.order[i];
                                }
                            },
                            error: function(data) {
                                alert(data.responseJSON.message);
                            }
                        });
                    }
                }
            });
        });
    </script>

@endsection
