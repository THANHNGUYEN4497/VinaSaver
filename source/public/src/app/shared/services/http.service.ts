import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/map';
import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/do';
import 'rxjs/add/operator/finally';
import { throwError } from 'rxjs';
import { Http, XHRBackend, RequestOptions, Request, RequestOptionsArgs, Response, Headers } from '@angular/http';

@Injectable()
export class HttpService extends Http {
    public pendingRequests: number = 0;
    public showLoading: boolean = false;

    constructor(backend:XHRBackend, defaultOptions:RequestOptions) {
        super(backend, defaultOptions);
    }

    request(url: string | Request, options?: RequestOptionsArgs): Observable<Response> {
        return this.intercept(super.request(url, options));
    }

    intercept(observable:Observable<Response>):Observable<Response> {
        this.turnOnModal();
        return observable
            .catch((err) => {
                //return Observable.throw(err);
                return throwError(err);
            })
            .do((res:Response) => {
            }, (err:any) => {
                console.log("Caught error: " + err);
            })
            .finally(() => {
                this.turnOffModal();
            });
    }

    private turnOnModal() {
        this.pendingRequests++;
        if (!this.showLoading) {
            this.showLoading = true;
            document.getElementsByTagName("body")[0].insertAdjacentHTML('beforeend', "<div id='loading_mark' style='z-index:100000;text-align:center;position:fixed;width:100%;height:100%;background:#fff;top:0px;left:0px;opacity:0.4;filter:alpha(opacity=40);'><img style='height:150px;margin-top:300px;width:150px;' src='assets/images/loader.gif'/></div>");
        }
        this.showLoading = true;
    }

    private turnOffModal() {
        this.pendingRequests--;
        if (this.pendingRequests <= 0) {
            if (this.showLoading) {
                var elem = document.getElementById("loading_mark");
                elem.parentNode.removeChild(elem);
            }
            this.showLoading = false;
        }
    }
}