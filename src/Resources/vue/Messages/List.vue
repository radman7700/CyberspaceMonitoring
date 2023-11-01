<template>
    <div class="row gutters">
		<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <table class="table">
                <tr>
                    <th></th>
                    <th>متن پیام</th>
                </tr>
                <tr v-for="item in MessageList">
                    <td></td>
                    <td>{{item.message}}</td>
                    <td>{{item.date}}</td>
                </tr>
            </table>
        </div>
    </div>
</template>

<script>

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
        }
    },
    components: {  },  
    mounted() {
        this.getMessageList();
    },
    methods: {
        getMessageList() {   
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                const token = response.data.token;
                axios.request({
                    method: 'GET',
                    url: this.getAppUrl() + 'api/payesh/telegram?action=getMessageList',
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
            this.getUsers(page,'');
            //this.getUsers(page,1,orderbyValue);
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