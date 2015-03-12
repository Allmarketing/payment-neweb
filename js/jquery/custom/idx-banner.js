(function($){
    function index_banner(wrapper){
        this.wrapper = wrapper;
        this.sid = null;
        this.cid = null;
        this.childrenNums = 0;
        this.direction = null;
        this.init = function(){
            this.wrapper.css('position','relative');
            this.wrapper.find('img').css({position:'absolute',left:0,top:0});
            this.wrapper.find('img:gt(0)').hide();
            this.childrenNums = this.wrapper.find('img').size();
            this.direction = $.index_banner_options.direction;
            var con = this;
            if($.index_banner_options.toRightId){
                $("#"+$.index_banner_options.toRightId).click(function(e){
                    e.preventDefault();
                    con.stop();
                    var id = con.cid+1;
                    con.direction='asc';
                    con.slideTo(id);
                    con.slide();
                });
            }
            if($.index_banner_options.toLeftId){
                $("#"+$.index_banner_options.toLeftId).click(function(e){
                    e.preventDefault();
                    con.stop();
                    var id = con.cid-1;
                    con.direction='desc';
                    con.slideTo(id);          
                    con.slide();
                });
            }
            if($.index_banner_options.thumbsId){
                $("#"+$.index_banner_options.thumbsId).children().click(function(e){
                    e.preventDefault();
                    con.stop();
                    var currentObject = this;
                    $(this).parent().children().each(function(idx,elm){
                        if(currentObject==elm){
                            con.slideTo(idx);
                        }
                    });
                    con.slide();
                });
            }
            this.cid = 0;
            this.slide();
        }
        this.slide = function(){
            var con = this;
            this.sid = setInterval(function(){
                var id = (con.direction=='asc')?con.cid+1:con.cid-1;
                con.slideTo(id);        
            },5000);
        }
        this.stop = function(){
            clearInterval(this.sid);
        }
        this.slideTo = function(id){
            var con = this;
            id = this.adjust_cid(id);
            con.wrapper.find('img:eq('+con.cid+')').fadeOut(500,function(){
                con.cid = id;
                con.wrapper.find('img:eq('+id+')').fadeIn(1000);
            });            
        }
        this.switch_direction = function(){
            this.direction = (this.direction=="asc")?"desc":"asc";
        }
        this.adjust_cid = function(cid){
            if(cid >= this.childrenNums){
                cid = 0;
            }       
            if(cid < 0){
                cid = this.childrenNums-1;
            }       
            return cid;
        }
        this.init();
    }
    $.fn.extend({
       'idx_banner': function(options){
           $.extend($.index_banner_options,options);
           var ib = new index_banner(this);
       } 
    });
    $.extend({
       'index_banner_options':{
           'direction':'asc', //asc or desc
           'toRightId':'',    //dom object id of go right button
           'toLeftId':'',     //dom object id of go left button
           'thumbsId':''      //thumbs list wraper id 
       } 
    });
})(jQuery);