<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class map extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.map';

    public static function canAccess(): bool
    {
        return Auth::user()->hasAnyRole(['HR', 'admin']);
    }
}
