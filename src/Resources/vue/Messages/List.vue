<template>
    <div class="card">
        <div class="card-body">
            <div class="accordion accordion-success custom-accordion">
                <div class="accordion-row open">
                    <a href="#" class="accordion-header">
                        <span>فیلتر‌ها</span>
                        <i class="accordion-status-icon close fa fa-plus"></i>
                        <i class="accordion-status-icon open fa fa-close"></i>
                    </a>
                    <div class="accordion-body">
                        <div class="row gutters form-row">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="search_text">از:</label>
                                    <date-picker v-model="date_start"  is-range mode="dateTime" :timezone="timezone"></date-picker>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="search_text">تا:</label>
                                    <date-picker v-model="date_end"></date-picker>
                                </div>
                            </div>                
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="search_user_id">شناسه کاربر:</label>
                                    <input class="form-control text-left" id="search_user_id" v-model="search_user_id" placeholder="شناسه کاربر را برای فیلتر کردن وارد کنید" dir="rtl">
                                </div>
                            </div> 
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="search_group_id">شناسه گروه:</label>
                                    <input class="form-control text-left" id="search_group_id" v-model="search_group_id" placeholder="شناسه گروه را برای فیلتر کردن وارد کنید" dir="rtl">
                                </div>
                            </div>                 
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                <label for="search_text">جستجو در پیام‌ها</label>
                                <textarea class="form-control" placeholder="جستجو اخبار" id="search_text" v-model="search_text"></textarea>
                            </div>
                            
                            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-12 col-12">
                                <br>
                                <button type="button" class="btn btn-primary" @click="isLoaded=false;getMessageList()"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>    
    <div class="alert alert-secondary" role="alert">
        تعداد پیام‌ها: {{MessageListCount}}
    </div>
    <br>
    <div class="row gutters">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12" v-if="!isLoaded">
            <div class="card">
                <div class="card-body text-center">
                    <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status">
                        <span class="sr-only">در حال بارگذاری ...</span> 
                    </div>    

                    <h4>در حال دریافت اطلاعات لطفا شکیبا باشد :) </h4>
                </div>    
            </div>    
        </div>
		<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12" v-else>
            <div class="card" v-for="(item, index)  in MessageList">
				<div class="card-header" style="background: cornsilk;">
                    <div class="row">
                        <div class="col-xl-5 col-lg-7 col-md-4 col-sm-12 col-12">
                            <h4><i class="fa fa-telegram" style="color:#55acee !important" aria-hidden="true"></i> {{item.telegram_group ? item.telegram_group.name : item.gid}} </h4>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                            <h4>
                                <i class="fa fa-user" aria-hidden="true" style="color:#dc3545 !important"></i> {{item.telegram_user ? item.telegram_user.first_name + ' ' + item.telegram_user.last_name +'<small>'+item.telegram_user.username+'</small>' : item.user_id}}
                            </h4>
                        </div>     
                        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-12 col-12 text-right" >
                            <h4>
                                <i class="fa fa-clock-o" aria-hidden="true" style="color:#28a745 !important"></i>
                                {{convertDateToPersian(item.date)}}
                            </h4>
                        </div>
                    </div>                                        
                </div>
				<div class="card-body">
					<p>{{item.message}}</p>
				</div>
			</div>            
        </div>
        <div class="col-sm-12" v-if="isLoaded">
            <nav aria-label="Page navigation" v-if="pagination.last_page != 1">
                <ul class="pagination justify-content-center pagination-rounded mb-3">
                    <li class="page-item" v-if="pagination.current_page > 1">
                        <a href="#" aria-label="Previous" class="page-link" @click.prevent="changePage(pagination.current_page - 1,orderbyValue)">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <li v-for="page in pagesNumber"
                        :class="[ page == isActived ? 'page-item active' : 'page-item']">
                        <a href="#" @click.prevent="changePage(page,orderbyValue)" class="page-link">{{ page }}</a>
                    </li>
                    <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                        <a href="#" aria-label="Next" class="page-link"
                            @click.prevent="changePage(pagination.current_page + 1,orderbyValue)">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>        
    </div>
</template>

<script>
import jalaliMoment from "jalali-moment";

export default {
    data() {
        return {
            MessageList:[],
            pagination: {
                total: 0,
                per_page: 2,
                from: 1,
                to: 0,
                current_page: 1,
                last_page: 1
            },
            offset:4,
            itemsPerPage:20, 
            search_text:'', 
            MessageListCount:0,
            date_start:'',
            date_end:'',
            search_user_id:'',    
            search_group_id:'',
            isLoaded:false,
        }
    },
    components: {  },  
    mounted() {
        this.getMessageList();
    },
    methods: {
        convertDateToPersian(date){
            return jalaliMoment(date, "YYYY-MM-DD HH:mm:ss").format("jYYYY/jMM/jDD HH:mm:ss");
        },    
        getMessageList(page=1,sort='') {   
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                const token = response.data.token;
                axios.request({
                    method: 'GET',
                    url: this.getAppUrl() + 'api/payesh/telegram?action=getMessageList&page='+page+'&search_text='+this.search_text+'&date_start='+this.date_start+'&date_end='+this.date_end+'&search_user_id='+this.search_user_id+'&search_group_id='+this.search_group_id,
                    headers: {'Authorization': `Bearer ${token}`}
                }).then(response => {
                    this.fetchPagesDetails(response.data.MessageList);        
                    this.MessageList = response.data.MessageList.data; 
                    this.MessageListCount =  response.data.MessageListCount;  
                    this.isLoaded=true;
                }).catch(error => {
                    this.checkError(error);
                });
            }).catch(error => {
                this.checkError(error);
            });
        },    
        fetchPagesDetails: function (page) {
        this.pagination = {
            total: page['total'],
            per_page: page['per_page'],
            from: page['from'],
            to: page['to'],
            current_page: page['current_page'],
            last_page: page['last_page'],
        };

        },
        changePage: function (page,orderbyValue) {
            this.isLoaded=false;
            this.getMessageList(page,'');
        },         
    },
    computed: {
      isActived () {
        return this.pagination.current_page;
      },
      pagesNumber () {
            if (!this.pagination.to) {
                return [];
            }
            let from = this.pagination.current_page - this.offset;
            if (from < 1) {
                from = 1;
            }
            let to = from + (this.offset * 2);
            if (to >= this.pagination.last_page) {
                to = this.pagination.last_page;
            }
            let pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;     
        } 
    }    
}
</script>

<style>
.vpd-icon-btn{
height: 36px;
}
</style>
