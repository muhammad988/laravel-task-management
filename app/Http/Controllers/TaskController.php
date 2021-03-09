<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\Order;
use App\Http\Requests\Task\Store;
use App\Http\Requests\Task\Update;
use App\Http\Resources\Task\TaskResource;
use App\Models\Task;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;
use function PHPUnit\Framework\isEmpty;

class TaskController extends Controller
{

    /**
     * @return Application|Factory|View
     */
    public function index(): View
    {
        $tasks = Task::orderBy('order', 'asc')->get();
        return view('task.index', compact('tasks'));
    }


    public function store(Store $request)
    {
//        try {
            $order = Task::orderBy('order', 'desc')->select('order')->first();
            if (isEmpty($order)){
                $request->offsetSet('order', $order->order+1 );
            }else{
                $request->offsetSet('order', 1 );
            }
        if (!isset($request->completed)){
            $request->offsetSet('completed', 0 );
        }
            print_r($request->all());
            $task = Task::create($request->all());
//            $data = (new  TaskResource($task));
//            return $this->respond_success_data(['task' => $data]);
//        } catch (Throwable $throwable) {
//            throw $throwable;
//        }
//        return redirect()->route('index');
    }
    public function order(Order $request)
    {
        try {


            if ($request->srcOrder< $request->dstOrder){
                $tasks = Task::whereBetween('order', [$request->srcOrder,$request->dstOrder])->get();
                foreach ($tasks as $task){
                    if ($task->order != $request->srcOrder){
                        Task::where('id',$task->id)->update(['order' =>$task->order-1]);
                    }
                }

            }else{
                $tasks = Task::whereBetween('order', [$request->dstOrder,$request->srcOrder])->get();
                foreach ($tasks as $task){
                    if ($task->order != $request->srcOrder){
                        Task::where('id',$task->id)->update(['order' =>$task->order+1]);
                    }
                }
//                Task::where('order',$request->srcOrder)->update(['order' =>$request->dstOrder]);

            }
        Task::where('order',$request->srcOrder)->where('id',$request->id)->update(['order' =>$request->dstOrder]);
        $orders = Task::orderBy('order', 'asc')->pluck('order');
        return $this->respond_success_data(['order' => $orders]);
        } catch (Throwable $throwable) {
            return $this->respond_error('common.something_wrong');

        }
//        return redirect()->route('index');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function edit(Task $task)
    {
        return view('task.edit', compact('task'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Update $request, Task $task)
    {

        $task->title = $request->title;
        $task->description = $request->description;
        if (isset($request->completed)){
            $task->completed = $request->completed;
        }else{
            $task->completed = 0;
        }
        $task->save();
        return redirect()->route('index');
    }


    public function destroy($id)
    {
        try {
           Task::destroy($id);
            return $this->respond_success('common.success');
        } catch (Throwable $throwable) {
            return $this->respond_error('common.something_wrong');

        }

    }
}
