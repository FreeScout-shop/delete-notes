<?php

namespace Modules\DeleteNotes\Http\Controllers;

use App\Thread;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\DeleteNotes\Providers\AvatarsServiceProvider;

class DeleteNotesController extends Controller
{
    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function delete(Request $request)
    {
        $response = [
            'status' => 'error',
            'msg'    => 'Unknown error', // this is error message
        ];

        $thread = Thread::find($request->thread_id ?? 0);
        if ($thread) {
            if (AvatarsServiceProvider::canDeleteNote($thread)) {
                $thread->delete();
                $response['status'] = 'success';
                $response['msg_success'] = __('Deleted note');
            } else {
                $response['msg'] = __('Not enough permissions');
            }
        } else {
            $response['msg'] = __('Thread not found');
        }


        if ($response['status'] == 'error' && empty($response['msg'])) {
            $response['msg'] = __('Unknown error occurred');
        }

        return \Response::json($response);
    }
}
