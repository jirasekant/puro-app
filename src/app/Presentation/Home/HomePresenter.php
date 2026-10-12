<?php

declare(strict_types=1);

namespace App\Presentation\Home;

use EasyRdf\Sparql\Client as SparqlClient;
use Nette\Application\UI\Presenter;

final class HomePresenter extends Presenter
{
    public function __construct(
        private readonly SparqlClient $sparqlClient,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        // 1. Fetch triples from Fuseki
        $result = $this->sparqlClient->query('
            SELECT ?s ?p ?o WHERE {
                ?s ?p ?o .
            } LIMIT 50
        ');

        $nodes = [];
        $edges = [];
        $nodeMap = [];

        foreach ($result as $row) {
            $subUri = (string) $row->s;
            $predUri = (string) $row->p;
            $objVal = (string) $row->o;

            // Shorten URIs for clean canvas labels
            $subLabel = $this->shortenUri($subUri);
            $objLabel = $this->shortenUri($objVal);
            $predLabel = $this->shortenUri($predUri);

            // Add Subject Node
            if (!isset($nodeMap[$subUri])) {
                $nodeMap[$subUri] = true;
                $nodes[] = [
                    'id' => $subUri,
                    'label' => $subLabel,
                    'color' => '#E0F7FA' // Distinct color for subjects/entities
                ];
            }

            // Check if Object is a URI or a Literal value
            if (filter_var($objVal, FILTER_VALIDATE_URL)) {
                if (!isset($nodeMap[$objVal])) {
                    $nodeMap[$objVal] = true;
                    $nodes[] = [
                        'id' => $objVal,
                        'label' => $objLabel,
                        'color' => '#FFF9C4'
                    ];
                }

                $edges[] = [
                    'from' => $subUri,
                    'to' => $objVal,
                    'label' => $predLabel
                ];
            } else {
                // Literal values attached as edge label or node property
                $literalId = md5($subUri . $predUri . $objVal);
                $nodes[] = [
                    'id' => $literalId,
                    'label' => '"' . $objLabel . '"',
                    'shape' => 'ellipse',
                    'color' => '#EEEEEE'
                ];
                $edges[] = [
                    'from' => $subUri,
                    'to' => $literalId,
                    'label' => $predLabel
                ];
            }
        }

        // Pass JSON payloads to Latte
        $this->template->nodes = json_encode($nodes);
        $this->template->edges = json_encode($edges);
    }

    private function shortenUri(string $uri): string
    {
        $parts = preg_split('/[#\/]/', $uri);
        return end($parts) ?: $uri;
    }
}