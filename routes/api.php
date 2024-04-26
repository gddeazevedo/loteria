<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;


Route::post('create-ticket', [TicketController::class, 'store'])->name('ticket.create');
Route::get('ticket/{ticket_code}', [TicketController::class, 'show'])->name('ticket.show');
