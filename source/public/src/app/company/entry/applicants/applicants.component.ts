import { Component, OnInit, ViewChild } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';

import { DataShareService, MoveFrom } from '../../shared/services/data-share.service';
import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';
import { ChatService } from '../../shared/services/chat.service';
import { ModalComponent } from '../../partials/modal/modal.component';
import { ChatComponent } from '../../entry/chat/chat.component';

@Component({
    selector: 'app-applicants',
    templateUrl: './applicants.component.html',
    styleUrls: ['./applicants.component.scss']
})
export class ApplicantsComponent implements OnInit {

    constructor(private dataShareService: DataShareService, private companyService: CompanyService, private restfulService: RestfulService, private router: Router, private route: ActivatedRoute, private chatService: ChatService) { }

    ngOnInit() {
        this.movedFrom = this.dataShareService.getMovedFrom();
        this.getJob();
        this.getList();
    }
    ngOnDestroy() {
        this.dataShareService.setMovedFrom(MoveFrom.Applicants);
    }
    private listApplicant: any;
    private job: any = {};
    private keyword = "";
    private status = "";

    private currentPage = 1;
    private perPage = 10;
    private paginates = [];
    private maxItem: number = 0;
    private indexElement = (this.currentPage - 1) * this.perPage;
    private jobId = this.route.snapshot.paramMap.get('jobId');

    private deleteId: number = 0;

    private movedFrom: MoveFrom;
    private MoveTo: typeof MoveFrom = MoveFrom;

    @ViewChild("modal") modal: ModalComponent;
    @ViewChild("modal_chat") modalChat: ChatComponent;
    
    getList() {
        let params = {
            'page_limit': this.perPage,
            'page_number': this.currentPage,
            'job_id': this.jobId,
            'keyword': this.keyword,
            'status': this.status
        };

        this.restfulService.doGet("company/job/applicant/list", params).subscribe(res => {
            if (res.success) {
                this.listApplicant = res.data.data;
                this.maxItem = res.data.total;
                this.indexElement = (this.currentPage - 1) * this.perPage;
            }
            else {
                this.router.navigate(["/company/job"]);
                console.log(res.error);
            }
        });
    }

    getJob() {
        this.restfulService.doGet("company/job/applicant/job/" + this.jobId, null).subscribe(res => {
            if (res.success) {
                this.job = res.data;
            }
            else {
                this.router.navigate(["/company/job"]);
                console.log(res.error);
            }
        });
    }

    search(form) {
        this.keyword = form.keyword;
        this.status = form.status;
        this.currentPage = 1;
        this.getList();
    }

    pageChanged(event) {
        this.currentPage = event;
        this.getList();
    }
    
    toggleModalDelete(jobId) {
        this.modal.confirm(`応募者を削除します。 <br>
            よろしければOKボタンを押してください`);
        this.deleteId = jobId;
    }

    confirmDelete(confirm: boolean) {
        if (confirm && this.deleteId) {
            this.restfulService.doPost("company/job/applicant/delete/" + this.deleteId, {}).subscribe(res => {
                if (res.success) {
                    if (this.listApplicant.length == 1 && this.currentPage > 1) {
                        this.currentPage = this.currentPage - 1;
                    }
                    this.deleteId = 0;
                    this.modal.toast('削除しました');
                    this.getList();
                }
                else {
                    console.log(res.error);
                }
            });
        }
    }

    chat(id){
        this.modalChat.detail(id);
    }

    goDetail(applicant, to) {
        this.dataShareService.setData({
            applicant,
            moveTo: to,
        });
        let url = '';
        switch (to) {
        case this.MoveTo.ApplicantDetail:
            url = `/company/job/${applicant.job_id}/applicant-detail/${applicant.id}`;
            break;
        case this.MoveTo.Determine:
            url = `/company/job/${applicant.job_id}/determine/${applicant.id}`;
            break;
        }
        this.router.navigate([url]);
    }
}
