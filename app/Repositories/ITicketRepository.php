<?php

namespace App\Repositories;

use App\Models\Ticket;

interface ITicketRepository
{
    public function find(string $code): ?Ticket;
    public function create(array $data): Ticket;
}