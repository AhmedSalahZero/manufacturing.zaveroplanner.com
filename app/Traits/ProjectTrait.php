<?php
namespace App\Traits;

use App\Project;
use App\Sharing;
trait ProjectTrait{
    public function project($link_code)
    {
        if (isset($link_code)) {
            $sharing_link =   Sharing::where('link_code',$link_code)->first();

            if (!isset($sharing_link) || $sharing_link->closed == 1) {
                $msg = json_encode(['en'=>__('This Link Has Been Expired'),'ar'=>__('لقد إنتهت صلاحية هذا الرابط')]);
                return abort(404,$msg);
            }
            $sharing_link->open	= 1;
            $sharing_link->save();
            return   Project::findOrFail($sharing_link->project_id);


        }

    }
}
