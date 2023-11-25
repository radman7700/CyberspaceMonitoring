<template>
    <div class="card">
        <div class="card-body">
            <div class="accordion accordion-success custom-accordion">
                <div class="accordion-row">
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
                                <button type="button" class="btn btn-primary" @click="isLoaded=false;refreshPage()"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> 
    <div class="row gutters">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card bg-primary">
                <div class="card-header d-flex justify-content-between">
                    کاربران تلگرام
                    <small class="opacity-5 primary-font"></small>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="font-size-35 font-weight-bold">{{telegramStatistics.UserCount}}</div>
                        <div class="icon-block icon-block-xl icon-block-floating icon-block-outline-white opacity-5">
                            <i class="fa fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>  
        </div> 
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card bg-success">
                <div class="card-header d-flex justify-content-between">
                    گروه‌های تلگرام
                    <small class="opacity-5 primary-font"></small>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="font-size-35 font-weight-bold">{{telegramStatistics.TelegramGroupCount}}</div>
                        <div class="icon-block icon-block-xl icon-block-floating icon-block-outline-white opacity-5">
                            <i class="fa fa-telegram"></i>
                        </div>
                    </div>
                </div>
            </div>  
        </div>          
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card bg-warning">
                <div class="card-header d-flex justify-content-between">
                    پست گروه‌های تلگرام
                    <small class="opacity-5 primary-font"></small>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="font-size-35 font-weight-bold">{{telegramStatistics.TelegramMessageCount}}</div>
                        <div class="icon-block icon-block-xl icon-block-floating icon-block-outline-white opacity-5">
                            <i class="fa fa-comment" style="color: ;"></i>
                        </div>
                    </div>
                </div>
            </div>  
        </div> 
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card bg-info">
                <div class="card-header d-flex justify-content-between">
                    نشست‌های فعال
                    <small class="opacity-5 primary-font"></small>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="font-size-35 font-weight-bold">{{telegramStatistics.sessionCount}}</div>
                        <div class="icon-block icon-block-xl icon-block-floating icon-block-outline-white opacity-5">
                            <i class="fa fa-phone"></i>
                        </div>
                    </div>
                </div>
            </div>  
        </div>                              
    </div>    
    <div class="row gutters">
		<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
		    <div class="card">
			    <div class="card-header">
				    <div class="card-title">روند انتشار</div>
				</div>
				<div class="card-body d-flex">
                    <Line :key="chartKey" ref="myChart" :data="data" :options="options" style="height: 350px;" />             
				</div>
			</div>
		</div>   
    </div>     
    <div class="row gutters">
		<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
		    <div class="card h-320">
			    <div class="card-header">
				    <div class="card-title">بالاترین کلمات کلیدی</div>
				</div>
				<div class="card-body d-flex">
				    <div id="my_favorite_latin_words" class="w-100" style="width:100%;height: 480px;"></div>
				</div>
			</div>
		</div>   
    </div>   

</template>

<script>

import {Chart as ChartJS,CategoryScale,LinearScale,PointElement,LineElement,Title,Tooltip,Legend} from 'chart.js'
import { Line } from 'vue-chartjs'
ChartJS.register(CategoryScale,LinearScale,PointElement,LineElement,Title,Tooltip,Legend)

export default {
    data() {
        return {
            chartKey:0,
            PayeshInformation:[],
            telegramStatistics:[],
            ReleaseProcess:[],
            data : {
                labels: [],
                datasets: [
                    {
                        label: 'روند انتشار',
                        backgroundColor: '#f87979',
                        data: []
                    }
                ]
            },
            options : {
                responsive: true,
                maintainAspectRatio: false
            },
            date_start:'',
            date_end:'',
            search_user_id:'',
            search_group_id:'',
            search_text:''
        }
    },
    components: { Line },  
    mounted() {
        this.getTelegramStatistics();
        this.getReleaseProcess();
        this.SystemMonitoringInformation();
    },
    methods: {
        refreshPage(){
            this.getTelegramStatistics();
            this.getReleaseProcess();
            this.SystemMonitoringInformation();        
        },
        SystemMonitoringInformation() {   
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                const token = response.data.token;
                axios.request({
                    method: 'GET',
                    url: this.getAppUrl() + 'api/payesh/information?action=home&search_text='+this.search_text+'&date_start='+this.date_start+'&date_end='+this.date_end+'&search_user_id='+this.search_user_id+'&search_group_id='+this.search_group_id,
                    headers: {'Authorization': `Bearer ${token}`}
                }).then(response => {
                    this.PayeshInformation = response.data.PayeshInformation;
                    this.wordCloudData = JSON.parse(this.PayeshInformation.wordCounts);
                    $("#my_favorite_latin_words").jQCloud(this.wordCloudData,{
                        autoResize: true,

                    });
                }).catch(error => {
                    this.checkError(error);
                });
            }).catch(error => {
                this.checkError(error);
            });
        },    
        getTelegramStatistics() {   
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                const token = response.data.token;
                axios.request({
                    method: 'GET',
                    url: this.getAppUrl() + 'api/payesh/information?action=telegramStatistics&search_text='+this.search_text+'&date_start='+this.date_start+'&date_end='+this.date_end+'&search_user_id='+this.search_user_id+'&search_group_id='+this.search_group_id,
                    headers: {'Authorization': `Bearer ${token}`}
                }).then(response => {
                    this.telegramStatistics = response.data.telegramStatistics;
                }).catch(error => {
                    this.checkError(error);
                });
            }).catch(error => {
                this.checkError(error);
            });
        },   
        getReleaseProcess() {   
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                const token = response.data.token;
                axios.request({
                    method: 'GET',
                    url: this.getAppUrl() + 'api/payesh/information?action=ReleaseProcess&search_text='+this.search_text+'&date_start='+this.date_start+'&date_end='+this.date_end+'&search_user_id='+this.search_user_id+'&search_group_id='+this.search_group_id,
                    headers: {'Authorization': `Bearer ${token}`}
                }).then(response => {
                    this.data.labels = response.data.ReleaseProcess.ReleaseProcessDates;
                    this.data.datasets[0].data = response.data.ReleaseProcess.ReleaseProcessCount; // اطمینان حاصل شود که به datasets[0].data دسترسی دارید
                     this.chartKey++;
                }).catch(error => {
                    this.checkError(error);
                });
            }).catch(error => {
                this.checkError(error);
            });
        },
    }
}
</script>

<style>
.vpd-icon-btn{height: 36px;}
</style>