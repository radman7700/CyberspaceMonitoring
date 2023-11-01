<template>
    <div class="row gutters">
        <div class="col-xl-3 col-lg-4 col-md-4 col-sm-4 col-6">
            <div class="info-tiles">
                <div class="info-icon">
                    <i class="icon-account_circle"></i>
                </div>
                <div class="stats-detail">
                    <h3>{{telegramStatistics.UserCount}}</h3>
                    <p>کاربر تلگرام </p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-4 col-sm-4 col-6">
            <div class="info-tiles">
                <div class="info-icon">
                    <i class="fa fa-location-arrow" aria-hidden="true"></i>
                </div>
                <div class="stats-detail">
                    <h3>{{telegramStatistics.TelegramGroupCount}}</h3>
                    <p>گروه‌های تلگرام</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-4 col-sm-4 col-6">
            <div class="info-tiles">
                <div class="info-icon">
                    <i class="icon-visibility"></i>
                </div>
                <div class="stats-detail">
                    <h3>{{telegramStatistics.TelegramMessageCount}}</h3>
                    <p>پست گروه‌های تلگرام</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-4 col-sm-4 col-6">
            <div class="info-tiles">
                <div class="info-icon">
                    <i class="icon-shopping_basket"></i>
                </div>
                <div class="stats-detail">
                    <h3>{{telegramStatistics.sessionCount}}</h3>
                    <p>نشست‌های فعال</p>
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