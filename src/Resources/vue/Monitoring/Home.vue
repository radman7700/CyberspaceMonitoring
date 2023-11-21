<template>
    <div class="row gutters">
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
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
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
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
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
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
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
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
				    <div id="my_favorite_latin_words" class="w-100"></div>
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
            }
        }
    },
    components: { Line },  
    mounted() {
        this.getTelegramStatistics();
        this.getReleaseProcess();
        this.SystemMonitoringInformation();
    },
    methods: {
        SystemMonitoringInformation() {   
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                const token = response.data.token;
                axios.request({
                    method: 'GET',
                    url: this.getAppUrl() + 'api/payesh/information?action=home',
                    headers: {'Authorization': `Bearer ${token}`}
                }).then(response => {
                    this.PayeshInformation = response.data.PayeshInformation;
                    this.wordCloudData = JSON.parse(this.PayeshInformation.wordCounts);
                    $("#my_favorite_latin_words").jQCloud(this.wordCloudData);
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
                    url: this.getAppUrl() + 'api/payesh/information?action=telegramStatistics',
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
                    url: this.getAppUrl() + 'api/payesh/information?action=ReleaseProcess',
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
