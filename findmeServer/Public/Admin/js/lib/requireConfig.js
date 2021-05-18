require.config({
	baseUrl:"./Public/Admin/js/lib",
	shim: {
	  'jquery.ajaxfileupload': {
	  	deps:['jquery']
	  },
	  'angular': {
	  	exports:'angular'
	  },
	  'WdatePicker': {
	  	exports:'WdatePicker'
	  },
	  'dialog':{
	  	exports:'dialog'
	  },
	  'layer': {
	  	deps:['jquery']
	  }
    },
	paths:{components:"../components",
			js:"../",
			ngcomponents:"../ngComponents",
			ngServices:"../ngServices",
			WdatePicker:"My97DatePicker/WdatePicker",
			dialog:"dialog-min",
			layer:"layer/layer"
		}
});