import { Component, OnInit } from '@angular/core';
import { RestfulService } from '../../../../shared/services/restful.service';
import { ActivatedRoute} from '@angular/router';

@Component({
  selector: 'app-applicant-list',
  templateUrl: './applicant-list.component.html',
  styleUrls: ['./applicant-list.component.scss']
})
export class ApplicantListComponent implements OnInit {
  private job_id: number;
  job: any;
  applicants:any;
  total : 0;
  number_per_page = 10;
  pager = 1;
  number_items = 0;
  model = {keyword: '',status:''};

  constructor(private restfulService:RestfulService,private activeRoute: ActivatedRoute) { }

  ngOnInit() {
    this.activeRoute.params.subscribe(params => {
      this.job_id = params['job_id'];
    });
    
    let data = {
      'job_id': this.job_id,
      'page_limit': this.number_per_page,
      'page_number': this.pager
    };
    this.getListApplicant(data);
    this.getJobInfo();
  }

  private getListApplicant(data:any) {
    let url = 'admin/job/applicant/list';
    this.restfulService.doGet(url, data).subscribe(commonResponse => this.handleResponse(commonResponse));
  }

  getJobInfo(){
    let url = 'admin/job/applicant/job/' + this.job_id;
    this.restfulService.doGet(url,null).subscribe(commonResponse => this.handleResponseobInfo(commonResponse));
  }

  private handleResponseobInfo(commonResponse : any){
    if(commonResponse==null) return;
      if (commonResponse.success) {
        this.job = commonResponse.data;
      } else {
          alert(commonResponse.error);
      }
  }

  search() {
    let data = {
      'job_id': this.job_id,
      'page_limit': this.number_per_page,
      'page_number': this.pager,
      'keyword': this.model.keyword,
      'status': this.model.status,
    };
    this.getListApplicant(data);
  }

  private handleResponse(commonResponse:any) {
		if(commonResponse==null) return;
        if (commonResponse.success) {
          this.applicants = commonResponse.data.data;
          this.total = commonResponse.data.total_items;
          for (let applicant of this.applicants) {
            var ageDifMs = Date.now()/1000 - applicant.birthday;
            applicant['age']= Math.floor(ageDifMs/31556926);
          }
        } else {
            alert(commonResponse.error);
        }
  }

  pageChanged(event){
    this.pager = event;
    this.search();
  }
}
