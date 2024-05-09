<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Jobs\PrizeDrawJob;
use App\Repositories\TicketRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends Controller
{

    public function __construct(private TicketRepository $ticketRepository) {}

    public function store(TicketRequest $request): JsonResponse
    {
        $ticket = $this->ticketRepository->create($request->all());
        PrizeDrawJob::dispatch($ticket);
        return response()->json(['ticketCode' => $ticket->code], Response::HTTP_CREATED);
    }

    public function show(string $code): JsonResponse
    {
        $ticket = $this->ticketRepository->find($code);
        return response()->json(new TicketResource($ticket));
    }
}
