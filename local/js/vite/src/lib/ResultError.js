class ResultError extends Error {
    constructor(message) {
        super(message);
        this.name = "ResultError";
    }
}
export default ResultError