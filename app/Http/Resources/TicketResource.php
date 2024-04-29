<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $machine_numbers = json_decode($this->machine_numbers, true);
        $message = 'not yet';
        $winner  = $this->numbers === $machine_numbers;
        
        if ($machine_numbers !== null) {
            $message = $winner ? 'you win' : 'you lost';
        }

        return [
            'name'=> $this->name,
            'yourNumbers' => $this->numbers,
            'machineNumbers' => $machine_numbers,
            'winner' => $winner,
            'message' => $message,
        ];
    }
}
