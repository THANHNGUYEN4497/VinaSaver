export abstract class UserService {
	
	constructor () {
	}
	
	public abstract getToken() : string;
	
	public abstract removeAll() : void;
}