<?php declare(strict_types=1);

namespace App\Model;

use EasyRdf\Sparql\Client as SparqlClient;


final class GraphFacade
{
	private const PuroNs = 'http://example.org/puro#';
	private const RdfType = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';
	private const RdfsLabel = 'http://www.w3.org/2000/01/rdf-schema#label';


	public function __construct(
		private readonly SparqlClient $sparqlClient,
	) {
	}


	/**
	 * Triples from Fuseki as Cytoscape.js elements (nodes first, then edges).
	 * @return list<array{data: array<string, string>}>
	 */
	public function getElements(int $limit = 500): array
	{
		$result = $this->sparqlClient->query("SELECT ?s ?p ?o WHERE { ?s ?p ?o . } LIMIT $limit");
		assert($result instanceof \EasyRdf\Sparql\Result);

		/** @var array<string, array{data: array<string, string>}> $nodes */
		$nodes = [];
		$edges = [];
		$labels = [];
		$kinds = [];

		foreach ($result as $row) {
			$s = (string) $row->s;
			$p = (string) $row->p;
			$isUri = $row->o instanceof \EasyRdf\Resource;
			$o = (string) $row->o;

			if ($p === self::RdfsLabel && !$isUri) {
				$labels[$s] = $o; // use as node caption instead of a separate literal node
				continue;
			}

			$nodes[$s] ??= $this->node($s);
			if ($p === self::RdfType && $o === self::PuroNs . 'BType') {
				$kinds[$s] = 'btype';
			} elseif ($p === self::RdfType && $o === self::PuroNs . 'BRelation') {
				$kinds[$s] = 'brelation';
			} elseif ($p === self::PuroNs . 'instanceOf') {
				$kinds[$s] = 'bobject';
			}

			if ($isUri) {
				$nodes[$o] ??= $this->node($o);
				$target = $o;
			} else {
				$target = md5($s . $p . $o);
				$nodes[$target] = ['data' => ['id' => $target, 'label' => $o, 'kind' => 'literal']];
			}

			$edges[] = ['data' => [
				'id' => md5("$s|$p|$target"),
				'source' => $s,
				'target' => $target,
				'label' => $this->shorten($p),
			]];
		}

		foreach ($nodes as $id => &$node) {
			if (isset($labels[$id])) {
				$node['data']['label'] = $labels[$id];
			}
			if (isset($kinds[$id])) {
				$node['data']['kind'] = $kinds[$id];
			}
		}

		return [...array_values($nodes), ...$edges];
	}


	/** @return array{data: array<string, string>} */
	private function node(string $uri): array
	{
		return ['data' => ['id' => $uri, 'label' => $this->shorten($uri), 'kind' => 'resource']];
	}


	private function shorten(string $uri): string
	{
		$parts = preg_split('/[#\/]/', $uri);
		return ($parts ? end($parts) : '') ?: $uri;
	}
}
