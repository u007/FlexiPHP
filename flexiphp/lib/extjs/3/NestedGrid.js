Ext.onReady( function() {

	var data = [
		['3m Co',71.72,0.02,0.03,'9/1 12:00am',false],
		['Alcoa Inc',29.01,0.42,1.47,'9/1 12:00am',false],
		['Altria Group Inc',83.81,0.28,0.34,'9/1 12:00am',false],
		['American Express Company',52.55,0.01,0.02,'9/1 12:00am',false],
		['American International Group, Inc.',64.13,0.31,0.49,'9/1 12:00am',false],
		['AT&T Inc.',31.61,-0.48,-1.54,'9/1 12:00am',false],
		['Boeing Co.',75.43,0.53,0.71,'9/1 12:00am',false],
		['Caterpillar Inc.',67.27,0.92,1.39,'9/1 12:00am',false],
		['Citigroup, Inc.',49.37,0.02,0.04,'9/1 12:00am',false],
		['E.I. du Pont de Nemours and Company',40.48,0.51,1.28,'9/1 12:00am',false],
		['Exxon Mobil Corp',68.1,-0.43,-0.64,'9/1 12:00am',true],
		['General Electric Company',34.14,-0.08,-0.23,'9/1 12:00am',true],
		['General Motors Corporation',30.27,1.09,3.74,'9/1 12:00am',false],
		['Hewlett-Packard Co.',36.53,-0.03,-0.08,'9/1 12:00am',false],
		['Honeywell Intl Inc',38.77,0.05,0.13,'9/1 12:00am',false],
		['Intel Corporation',19.88,0.31,1.58,'9/1 12:00am',false],
		['International Business Machines',81.41,0.44,0.54,'9/1 12:00am',false],
		['Johnson & Johnson',64.72,0.06,0.09,'9/1 12:00am',false],
		['JP Morgan & Chase & Co',45.73,0.07,0.15,'9/1 12:00am',true],
		['McDonald\'s Corporation',36.76,0.86,2.40,'9/1 12:00am',false],
		['Merck & Co., Inc.',40.96,0.41,1.01,'9/1 12:00am',false],
		['Microsoft Corporation',25.84,0.14,0.54,'9/1 12:00am',false],
		['Pfizer Inc',27.96,0.4,1.45,'9/1 12:00am',false],
		['The Coca-Cola Company',45.07,0.26,0.58,'9/1 12:00am',true],
		['The Home Depot, Inc.',34.64,0.35,1.02,'9/1 12:00am',false],
		['The Procter & Gamble Company',61.91,0.01,0.02,'9/1 12:00am',false],
		['United Technologies Corporation',63.26,0.55,0.88,'9/1 12:00am',false],
		['Verizon Communications',35.57,0.39,1.11,'9/1 12:00am',true],
		['Wal-Mart Stores, Inc.',45.45,0.73,1.63,'9/1 12:00am',false]
	];

	

	

  /*
	var panel = new Ext.Panel({
		title      : "Nested Grids Example",
		autoHeight : true,
		items      : [getGrid( data)],
		applyTo    : 'grid-box'
	});
  */

});

/**
	 * Custom function used for column renderer
	 * @param {Object} val
	 */
	function change( val) {
		if (val > 0) {
			return '<span style="color:green;">' + val + '</span>';
		} else if (val < 0) {
			return '<span style="color:red;">' + val + '</span>';
		}
		return val;
	};

	/**
	 * Custom function used for column renderer
	 * @param {Object} val
	 */
	function pctChange( val) {
		if (val > 0) {
			return '<span style="color:green;">' + val + '%</span>';
		} else if (val < 0) {
			return '<span style="color:red;">' + val + '%</span>';
		}
		return val;
	};

var getGrid = function( data, element) {
		var store = new Ext.data.ArrayStore({
			fields: [
				{ name: 'company' },
				{ name: 'price', type: 'float' },
				{ name: 'change', type: 'float' },
				{ name: 'pctChange', type: 'float' },
				{ name: 'lastChange', type: 'date', dateFormat: 'n/j h:ia' },
				{ name: 'is_leaf', type: 'bool' }
			],
			data: data
		});

		var expander = new Ext.ux.grid.RowExpander({
			tpl              : '<div class="ux-row-expander-box"></div>',
			actAsTree        : true,
			treeLeafProperty : 'is_leaf',
			listeners        : {
				expand : function( expander, record, body, rowIndex) {
					getGrid( data, Ext.get( this.grid.getView().getRow( rowIndex)).child( '.ux-row-expander-box'));
					//alert( Ext.ComponentMgr.all.length);
				}
			}
		});
    var sm = new Ext.grid.CheckboxSelectionModel();

		var grid = new Ext.grid.GridPanel({
			store: store,
			columns: [
				expander,
        sm,
				{ id:'company',header: 'Company', width: 160, sortable: true, dataIndex: 'company' },
				{ header: 'Price', width: 75, sortable: true, renderer: 'usMoney', dataIndex: 'price' },
				{ header: 'Change', width: 75, sortable: true, renderer: change, dataIndex: 'change' },
				{ header: '% Change', width: 75, sortable: true, renderer: pctChange, dataIndex: 'pctChange' },
				{ header: 'Last Updated', width: 85, sortable: true, renderer: Ext.util.Format.dateRenderer('m/d/Y'), dataIndex: 'lastChange' }
			],
			stripeRows: true,
			autoExpandColumn: 'company',
			autoHeight: true,
			border: false,
			width: '100%',
      sm: sm,
			stateful: true,
			stateId: 'grid',
			plugins: expander
		});

		element && grid.render( element);
		return grid;
	};
