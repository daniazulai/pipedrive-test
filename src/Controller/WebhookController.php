<?php

namespace App\Controller;

use App\Service\PipedriveService;
use Exception;
use Pipedrive\Api\DealFieldsApi;
use Pipedrive\Api\DealsApi;
use Pipedrive\Api\NotesApi;
use Pipedrive\Api\StagesApi;
use Pipedrive\Model\AddNoteRequest;
use Pipedrive\Model\FieldUpdateRequest;
use Pipedrive\Model\UpdateDealRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebhookController extends AbstractController
{
    public function __construct(
        private readonly PipedriveService $pipedriveService
    ) {
    }

    #[Route(path: '/webhook/pipedrive', name: 'webhook')]
    public function webhook(): Response
    {
        $result = file_get_contents('php://input');
        $data = json_decode($result, true);

        try {
            $this->processWebhook($data['meta']);
            return new Response();
        } catch (Exception $e) {}

        return new Response(null, 404);
    }

    private function processWebhook(array $data):void
    {
        if ($data['object'] === 'deal') {
            if ($data['action'] === 'added' || $data['action'] === 'updated') {
                $dealId = $data['id'];

                // todo: update payment afterwards based on payment in advance
                // Payment in advance: e05c232c5ec976bdff3d8143af2a240262149c9e
                // Payment afterwards: e3952d14a4de5dc45f04a6ea070094c84e98c278

                // todo: deal value < 50 = low value
                // todo: deal value > 50 = high value
                // todo: if deal is won > won deals
                $lowValueStageId = 2;
                $highValueStageId = 3;
                $wonDealsStageId = 4;
                /** @var DealsApi $dealsApi */
                $dealsApi = $this->pipedriveService->getApiInstance('Deals');
                $dealsApi->updateDeal($dealId, new UpdateDealRequest([
                    'stage_id' => $lowValueStageId,
                ]));

                // todo: add note when status is changed
                /** @var NotesApi $notesApi */
                $notesApi = $this->pipedriveService->getApiInstance('Notes');
                $notesApi->addNote(new AddNoteRequest([
                    'content' => 'status x -> status y',
                    'deal_id' => $dealId,
                ]));
            }
        }
    }
}
