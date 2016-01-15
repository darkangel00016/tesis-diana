/**
 * Created by lerny on 12/12/15.
 */
var events = function (event, currentValue) {
    this.isNumeric = event.which >= 48 && event.which <= 57;
    this.isArrow = event.which >= 37 && event.which <= 40;
    this.isPoint = event.which == 46;
    this.isDelete = event.which == 8 || (event.keyCode == 46 && event.which == 0);
    this.currentValue = currentValue;
    this.validateNumber = function () {
        return this.isDelete || this.isNumeric || this.isArrow;
    };
    this.validateReal = function () {
        if(this.currentValue != "" && this.isPoint && this.currentValue.match(/\./)) {
            return false;
        }
        return this.isDelete || this.isNumeric || this.isArrow || this.isPoint;
    };
};