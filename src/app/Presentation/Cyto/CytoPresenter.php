<?php declare(strict_types=1);

namespace App\Presentation\Cyto;

use App\Model\GraphFacade;
use Nette\Application\UI\Presenter;
use Nette\Utils\Json;


final class CytoPresenter extends Presenter
{
	public function __construct(
		private readonly GraphFacade $graph,
	) {
		parent::__construct();
	}


	public function renderDefault(): void
	{
		$this->template->elements = Json::encode($this->graph->getElements());
	}
}
