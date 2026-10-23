import cytoscape from 'cytoscape';

const container = document.getElementById('cy');
const elements = JSON.parse(container.dataset.elements);

const cy = cytoscape({
	container,
	elements,
	style: [
		{
			selector: 'node',
			style: {
				'label': 'data(label)',
				'text-valign': 'center',
				'text-halign': 'center',
				'font-size': 12,
				'shape': 'round-rectangle',
				'width': 'label',
				'height': 'label',
				'padding': '10px',
				'background-color': '#E0F7FA',
				'border-width': 1,
				'border-color': '#90A4AE',
			},
		},
		{ selector: 'node[kind = "btype"]', style: { 'background-color': '#FFF9C4' } },
		{ selector: 'node[kind = "brelation"]', style: { 'background-color': '#F8BBD0', 'shape': 'diamond', 'padding': '16px' } },
		{ selector: 'node[kind = "bobject"]', style: { 'background-color': '#C8E6C9' } },
		{ selector: 'node[kind = "literal"]', style: { 'background-color': '#EEEEEE', 'shape': 'ellipse' } },
		{
			selector: 'edge',
			style: {
				'label': 'data(label)',
				'font-size': 10,
				'curve-style': 'bezier',
				'target-arrow-shape': 'triangle',
				'line-color': '#90A4AE',
				'target-arrow-color': '#90A4AE',
				'text-background-color': '#fafafa',
				'text-background-opacity': 1,
				'text-rotation': 'autorotate',
				'width': 1.5,
			},
		},
		{ selector: ':selected', style: { 'border-width': 3, 'border-color': '#1976D2' } },
	],
	layout: { name: 'cose', animate: false },
});

const select = document.getElementById('layout');
select?.addEventListener('change', () => {
	cy.layout({ name: select.value, animate: true }).run();
});
document.getElementById('fit')?.addEventListener('click', () => cy.fit(undefined, 30));

window.cy = cy; // for the upcoming editing features
