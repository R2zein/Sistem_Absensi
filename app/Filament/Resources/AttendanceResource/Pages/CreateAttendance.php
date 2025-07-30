<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Auth;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;

    protected function mutateFormDatabeforecreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        $data['status']='pending';
        return $data;
    }
}
