<?php

namespace App\Actions;

use App\DTOs\ExecuteDrawDTO;
use App\Exceptions\Domain\DrawAlreadyExecutedException;
use App\Exceptions\Domain\RaffleNotActiveException;
use App\Models\Raffle;
use App\Services\AuditHashService;
use App\Services\DrawService;
use Illuminate\Support\Collection;

class ExecuteDrawAction
{
    public function __construct(
        private readonly DrawService      $drawService,
        private readonly AuditHashService $auditHashService,
    ) {}

    /**
     * @throws RaffleNotActiveException
     * @throws DrawAlreadyExecutedException
     */
    public function execute(ExecuteDrawDTO $dto): Collection
    {
        $raffle = Raffle::findOrFail($dto->raffleId);

        if ($raffle->status !== 'active') {
            throw new RaffleNotActiveException('La rifa debe estar activa para ejecutar el sorteo.');
        }

        if ($raffle->draw_date && $raffle->draw_date->isFuture()) {
            throw new RaffleNotActiveException('La fecha de sorteo aún no ha llegado.');
        }

        return $this->drawService->execute($dto);
    }
}
