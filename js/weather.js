let weather = {
    apiKey: "37ccc843e660d552aeafe5b8a89a9632",
    fetchWeather: function (city) {
        fetch(
            "https://api.openweathermap.org/data/2.5/weather?q="
            + city
            + "&units=metric&appid="
            + this.apiKey
        ).then((response) => response.json())
            .then((data) => {
                this.displayWeather(data)
            })
    },
    displayWeather: function (data) {
        const { name } = data
        const { icon, description } = data.weather[0]
        const { temp, humidity } = data.main
        const { speed } = data.wind

        document.querySelector(".weather__city").innerText = "Weather in " + name
        document.querySelector(".weather__icon").src = "https://openweathermap.org/img/wn/" + icon + "@2x.png"
        document.querySelector(".weather__description").innerText = description
        document.querySelector(".weather__temp__big").innerText = `${Math.floor(temp)} °C`
        document.querySelector(".weather__humidity").innerText = "Humidity: " + humidity + "%"
        document.querySelector(".weather__wind").innerText = `Wind speed: ${speed} km/h`
    },
    fetchForecast: function (city) {
        fetch(
            "https://api.openweathermap.org/data/2.5/forecast?q="
            + city
            + "&units=metric&appid="
            + this.apiKey
        ).then((response) => response.json())
            .then((data) => {
                this.displayForecast(data)
            })
    },
    displayForecast: function (data) {
        var singleDays = data.list.filter((entry) => entry.dt_txt.includes("15:00:00")) // filter only the 15:00:00 entrys
        var i = 1
        singleDays.forEach(entry => {
            var date = new Date(entry.dt_txt.split(' ')[0]).toString().split(' ')
            document.getElementById("weatherPrevDate" + i).innerText = date[0] + ', ' + date[1] + ' ' + date[2]
            document.getElementById("weatherPrevIcon" + i).src = "https://openweathermap.org/img/wn/" + entry.weather[0].icon + ".png"
            document.getElementById("weatherPrevTemp" + i).innerText = `${Math.floor(entry.main.temp)} °C`
            i++
        });

    }
}