import { Injectable } from '@angular/core';

export enum MoveFrom {
    NULL=0,
    Company = 1,
    Login,
    JobList,
    JobDetail,
    JobAdd,
    JobEdit,

    StaffList,
    StaffAdd,
    StaffEdit,

    CompanyEdit,
    CompanyInfo,

    SettlementList,

    Applicants,
    Chat,
    ApplicantDetail,
    Determine,
    Recruit,
    Bonus,
    Report,
    Settle,
    NewApplicants,
    ChatList,
}

@Injectable({
    providedIn: 'root'
})
export class DataShareService {
    private movedFrom: MoveFrom = MoveFrom.NULL;
    private data: any;

	constructor () {
	}
    public setData(data: any) {
        this.data = data;
    }
    public getData(): any {
        return this.data;
    }
    public setMovedFrom(from: MoveFrom) {
        this.movedFrom = from;
    }
    public getMovedFrom(): MoveFrom {
        return this.movedFrom;
    }
}
