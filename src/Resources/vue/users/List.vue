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
                            
                            <div class="col-xl-8 col-lg-8 col-md-4 col-sm-12 col-12 text-right">
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-12 col-12 text-right">
                                <br>
                                <button type="button" class="btn btn-primary" @click="isLoaded=false;getUserList()"><i class="fa fa-search"></i> جستجو عادی</button>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-12 col-12 text-right">
                                <br>
                                <button type="button" class="btn btn-info" @click="isLoaded=false;type=2;getUserList(1,2)"><i class="fa fa-search"></i> سوژه یابی</button>
                            </div>   
                            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-12 col-12 text-right">
                                <br>
                                <button type="button" class="btn btn-info" @click="isLoaded=false;type=2;exportUserList2()"><i class="fa fa-excel"></i> اکسل سوژه‌ها </button>
                            </div>                                                       
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
            <div class="table-responsive" tabindex="3" style="overflow: hidden; outline: none;">
                <div class="card">
                    <div class="card-body text-center">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">شناسه</th>
                                    <th scope="col">نام</th>
                                    <th scope="col">نام خانوادگی</th>
                                    <th scope="col">نام کاربری</th>
                                    <th scope="col" v-if="type==2">تعداد پیام‌ها</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in UserList">
                                    <th scope="row">{{(R + searchPageNumber * (pagination.current_page - 1)) + 1}}</th>
                                    <td>{{ item.user_id }}</td>
                                    <td v-if="type==1">{{ item.first_name }}</td><td v-else>{{ getSafe(()=>item.telegram_user.first_name)  }}</td>
                                    <td v-if="type==1">{{ item.last_name }}</td><td v-else>{{ getSafe(()=>item.telegram_user.last_name)}}</td>
                                    <td v-if="type==1">{{ item.username }}</td><td v-else>{{ getSafe(()=>item.telegram_user.username)}}</td>
                                    <td v-if="type==2">{{ item.total_messages }}</td>
                                </tr>
                            </tbody>                        
                        </table>
                    </div>
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

export default {
    data() {
        return {
            UserList:[],
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
            search_user_id:'',    
            search_group_id:'',
            isLoaded:false,
            type:1,
        }
    },
    components: {  },  
    mounted() {
        this.getUserList();
    },
    methods: {
        exportUserList2() {  
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                const token = response.data.token;
                axios.request({
                    method: 'GET',
                    url: this.getAppUrl() + 'api/payesh/telegram?action=exportSubjectUsers&search_text='+this.search_text+'&search_user_id='+this.search_user_id+'&search_group_id='+this.search_group_id,
                    headers: {'Authorization': `Bearer ${token}`}
                }).then(response => {
                    this.fetchPagesDetails(response.data.UserList);        
                    this.UserList = response.data.UserList.data; 
                    this.UserCount = response.data.UserCount.data; 
                    this.isLoaded=true;
                }).catch(error => {
                    this.checkError(error);
                });
            }).catch(error => {
                this.checkError(error);
            });
        },    
        getUserList(page=1,type=1) {  
            console.log(this.type)
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                const token = response.data.token;
                axios.request({
                    method: 'GET',
                    url: this.getAppUrl() + 'api/payesh/telegram?action=getUserList&page='+page+'&search_text='+this.search_text+'&search_user_id='+this.search_user_id+'&search_group_id='+this.search_group_id+'&type='+type,
                    headers: {'Authorization': `Bearer ${token}`}
                }).then(response => {
                    this.fetchPagesDetails(response.data.UserList);        
                    this.UserList = response.data.UserList.data; 
                    this.UserCount = response.data.UserCount.data; 
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
            this.getUserList(page,'');
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
