<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Queue;
use App\Jobs\PrizeDrawJob;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;


class TicketControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_ticket_and_if_prize_draw_job_is_pushed_and_show_ticket(): void
    {
        Queue::fake();
        
        $response = $this->postJson("/api/create-ticket", [
            'name' => 'Punter Name',
            'numbers' => [1, 2, 3, 4, 5, 6],
        ]);

        $response->assertStatus(Response::HTTP_CREATED)->assertExactJson([
            'ticketCode' => $response['ticketCode']
        ]);

        Queue::assertPushed(PrizeDrawJob::class, 1);

        $ticket_code = $response['ticketCode'];
        $ticket = Ticket::find($ticket_code);
        $response = $this->getJson("/api/ticket/$ticket_code");

        $response->assertStatus(Response::HTTP_OK)->assertExactJson([
            'name' => 'Punter Name',
            'yourNumbers' => [1, 2, 3, 4, 5, 6],
            'machineNumbers' => null,
            'winner' => false,
            'message' => 'not yet'
        ]);

        (new PrizeDrawJob($ticket))->handle();

        $response = $this->getJson("/api/ticket/$ticket_code");

        $response->assertStatus(Response::HTTP_OK)->assertJson(fn (AssertableJson $json) =>
            $json->where('name', 'Punter Name')
                 ->where('yourNumbers', [1, 2, 3, 4, 5, 6])
                 ->whereNot('machineNumbers', null)
                 ->where('winner', [1, 2, 3, 4, 5, 6] === json_decode($ticket->machine_numbers))
                 ->whereNot('message', 'not yet')
        );
    }

    public function test_show_ticket_possibilities(): void
    {
        $ticket = new Ticket();
        $ticket->code = uniqid();
        $ticket->name = 'Punter Name';
        $ticket->numbers = [1,2,3,4,5,6];
        $ticket->save();

        $request = $this->getJson("/api/ticket/$ticket->code");
        $request->assertStatus(Response::HTTP_OK)->assertExactJson([
            'name' => 'Punter Name',
            'yourNumbers' => [1, 2, 3, 4, 5, 6],
            'machineNumbers' => null,
            'winner' => false,
            'message' => 'not yet'
        ]);

        $ticket->machine_numbers = [1, 2, 3, 4, 5, 6];
        $ticket->save();

        $request = $this->getJson("/api/ticket/$ticket->code");
        $request->assertStatus(Response::HTTP_OK)->assertExactJson([
            'name' => 'Punter Name',
            'yourNumbers' => [1, 2, 3, 4, 5, 6],
            'machineNumbers' => [1, 2, 3, 4, 5, 6],
            'winner' => true,
            'message' => 'you win'
        ]);

        $ticket->machine_numbers = [1, 2, 3, 4, 5, 5];
        $ticket->save();

        $request = $this->getJson("/api/ticket/$ticket->code");
        $request->assertStatus(Response::HTTP_OK)->assertExactJson([
            'name' => 'Punter Name',
            'yourNumbers' => [1, 2, 3, 4, 5, 6],
            'machineNumbers' => [1, 2, 3, 4, 5, 5],
            'winner' => false,
            'message' => 'you lost'
        ]);
    }


    public function test_create_ticket_name_rules(): void
    {
        $response = $this->postJson('/api/create-ticket', [
            'name' => 'P',
            'numbers' => [1, 2, 3, 4, 5, 6],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJson(fn (AssertableJson $json) =>
                    $json->where('message', 'The name field must be at least 2 characters.')
                        ->etc()
                );

        $response = $this->postJson('/api/create-ticket', [
            'name' => str_repeat('a', 51),
            'numbers' => [1, 2, 3, 4, 5, 6],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJson(fn (AssertableJson $json) =>
                    $json->where('message', 'The name field must not be greater than 50 characters.')
                        ->etc()
                );

        $response = $this->postJson('/api/create-ticket', [
            'name' => null,
            'numbers' => [1, 2, 3, 4, 5, 6],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJson(fn (AssertableJson $json) =>
                    $json->where('message', "The name field is required.")
                        ->etc()
                );
    }


    public function test_create_ticket_numbers_rules(): void
    {
        $response = $this->postJson('/api/create-ticket', [
            'name' => 'Punter Name',
            'numbers' => [1, 2, 3, 4, 5],
        ]);
        
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJson(fn (AssertableJson $json) =>
                    $json->where('message', "The numbers field must have at least 6 items.")
                        ->etc()
                );

        $response = $this->postJson('/api/create-ticket', [
            'name' => 'Punter Name',
            'numbers' => [1, 2, 3, 4, 5, 6, 7],
        ]);
        
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJson(fn (AssertableJson $json) =>
                    $json->where('message', "The numbers field must not have more than 6 items.")
                        ->etc()
                );

        $response = $this->postJson('/api/create-ticket', [
            'name' => 'Punter Name',
            'numbers' => null,
        ]);
        
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJson(fn (AssertableJson $json) =>
                    $json->where('message', "The numbers field is required.")
                        ->etc()
                );
    }
}
