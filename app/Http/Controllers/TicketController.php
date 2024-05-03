<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Jobs\PrizeDrawJob;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends Controller
{
    public function store(TicketRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $ticket_code = uniqid();
        $ticket = Ticket::create([
            'code' => $ticket_code,
            ...$validated
        ]);

        PrizeDrawJob::dispatch($ticket);

        return response()->json(['ticketCode' => $ticket_code], Response::HTTP_CREATED);
    }

    public function show(string $code): JsonResponse
    {
        try {
            $ticket = Ticket::findOrFail($code);
        } catch (ModelNotFoundException $e) {
            return response()->json(
                ['message' => "ticket with code $code was not found"], Response::HTTP_NOT_FOUND);
        }

        return response()->json(new TicketResource($ticket));
    }
}
