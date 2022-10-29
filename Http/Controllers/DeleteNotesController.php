<?php

namespace Modules\DeleteNotes\Http\Controllers;

use App\Thread;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\DeleteNotes\Providers\DeleteNotesServiceProvider;

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
            $user = auth()->user();

            if (DeleteNotesServiceProvider::canDeleteNote($thread, $user)) {
                if (!$user->isAdmin()){
                    Thread::create($thread->conversation, Thread::TYPE_LINEITEM, '', [
                        'user_id'       => $user->id,
                        'created_by_customer_id' => $thread->conversation->customer_id,
                        'action_type' => DeleteNotesServiceProvider::ACTION_TYPE_DELETE,
                        'source_via'    => Thread::PERSON_USER,
                        'source_type'   => Thread::SOURCE_TYPE_WEB,
                    ]);
                }

                $thread->delete();

                $thread->conversation->setPreview();
                $thread->conversation->save();

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
