mop.modules.List = new Class({
	Extends: mop.modules.MoPList,
	/* Section: Getters & Setters */
	getAddObjectURL: function(){ return  mop.util.getBaseURL() + "ajax/html/"+this.getSubmissionController()+"/addObject/" + this.getObjectId(); },
	getSubmitSortOrderURL: function(){ return mop.util.getBaseURL()  + "ajax/html/" + this.getSubmissionController() + "/saveSortOrder/" + this.getObjectId(); },
	getRemoveObjectURL: function(){ return mop.util.getBaseURL() + "ajax/data/list/removeObject/" + this.getObjectId(); },
	toString: function(){ return "[ object, mop.MoPObject, mop.modules.Module, mop.modules.MoPList, mop.modules.List ]"; },
	initialize: function( anElement, aMarshal, options ){
        this.parent( anElement, aMarshal, options );
	}
});