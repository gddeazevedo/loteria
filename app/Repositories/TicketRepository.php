<?php

namespace App\Repositories;

use App\Models\Ticket;

class TicketRepository implements ITicketRepository
{
    public function find(string $code): Ticket
    {
        return Ticket::find($code);    
    }

    public function create(array $data): Ticket
    {
        $code = uniqid();
        return Ticket::create(["code" => $code, ...$data]);
    }
}
