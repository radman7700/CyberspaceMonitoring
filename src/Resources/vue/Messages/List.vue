<template>
    <div class="row gutters">
		<div class="col-xl-10 col-lg-10 col-md-8 col-sm-12 col-12">
            <input v-model="search_text" class="form-control" placeholder="جستجو اخبار">
        </div>
		<div class="col-xl-2 col-lg-2 col-md-4 col-sm-12 col-12">
            <button type="button" class="btn btn-primary"><i class="fa fa-search" @click="getMessageList()"></i></button>
        </div>
    </div>
    <br>
    <div class="row gutters">
		<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>R</th>
                            <th>شناسه کاربر</th>
                            <th>نام گروه</th>
                            <th>تاریخ ارسال</th>
                        </tr>
                    </thead>                
                    <tbody v-for="(item, index)  in MessageList">
                        <tr>
                            <td class="align-middle">{{(index + itemsPerPage * (pagination.current_page - 1)) + 1}}</td>
                            <td>{{item.user_id}}</td>
                            <td>{{item.telegram_group ? item.telegram_group.name : item.gid}}</td>
                            <td>{{convertDateToPersian(item.date)}}</td>
                        </tr>
                        <tr>
                            <td colspan="4">{{item.message}}</td>
                        </tr>                        
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-sm-12">
            <nav aria-label="Page navigation" v-if="pagination.last_page != 1">
                <ul class="pagination">
                    <li v-if="pagination.current_page > 1">
                        <a href="#" aria-label="Previous" class="page-link" @click.prevent="changePage(pagination.current_page - 1,orderbyValue)">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <li v-for="page in pagesNumber"
                        :class="[ page == isActived ? 'page-item active' : '']">
                        <a href="#" @click.prevent="changePage(page,orderbyValue)" class="page-link">{{ page }}</a>
                    </li>
                    <li v-if="pagination.current_page < pagination.last_page">
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
                    url: this.getAppUrl() + 'api/payesh/telegram?action=getMessageList&page='+page+'&search_text='+this.search_text,
                    headers: {'Authorization': `Bearer ${token}`}
                }).then(response => {
                    this.fetchPagesDetails(response.data.MessageList);        
                    this.MessageList = response.data.MessageList.data;                
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