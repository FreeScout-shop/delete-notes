<?php

Route::group(['middleware' => 'web', 'prefix' => \Helper::getSubdirectory(), 'namespace' => 'Modules\DeleteNotes\Http\Controllers'], function()
{
    Route::post('/delete-note/{thread_id}', ['uses' => 'DeleteNotesController@delete', 'laroute' => true])->name('deletenotes.delete');
});
