<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Jobs\PrizeDrawJob;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'name' => ['required', 'max:50'],
            'numbers' => ['required', 'array', 'min:6', 'max:6']
        ];

        $validated = $request->validate($rules);
        $ticket_code = uniqid();
        $ticket = Ticket::create([
            'code' => $ticket_code,
            ...$validated
        ]);

        PrizeDrawJob::dispatch($ticket);

        return response()->json(['ticketCode' => $ticket_code], 201);
    }

    public function show(string $code): JsonResponse
    {
        try {
            $ticket = Ticket::findOrFail($code);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => "ticket with code $code was not found"], 404);
        }

        $machine_numbers = json_decode($ticket->machine_numbers, true);

        $res = [
            'name'=> $ticket->name,
            'yourNumbers' => $ticket->numbers,
            'machineNumbers' => $machine_numbers,
            'winner' => $ticket->numbers === $machine_numbers,
        ];

        if ($machine_numbers === null) {
            $res['message'] = 'not yet';
            return response()->json($res);
        }

        $res['message'] = $res['winner'] ? 'you win' : 'you lost';

        return response()->json($res);
    }
}
