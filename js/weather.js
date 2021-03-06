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
        const LOWEST = 0
        const HIGHEST = 1
        const ICON = 2
        const daysForecast = new Map()
        data = data.list.filter((entry) => !entry.dt_txt.includes(new Date().toISOString().slice(0, 10))) // delete current day from forecast entrys
        data.forEach(entry => {
            var date = entry.dt_txt.split(' ')[0]
            const temp  = entry.main.temp
            const icon = entry.weather[0].icon
            var singleForecastDay = daysForecast.get(date)
            if (!singleForecastDay) {
                daysForecast.set(date, [temp, temp, icon])
            } else {
                if (singleForecastDay[LOWEST] > temp) singleForecastDay[LOWEST] = temp
                if (singleForecastDay[HIGHEST] < temp) singleForecastDay[HIGHEST] = temp
                if (entry.dt_txt.includes("15:00:00")) singleForecastDay[ICON] = icon
            }
        });

        var i = 1
        daysForecast.forEach((val, key) => {
            var date = new Date(key).toString().split(' ')
            document.getElementById("weatherPrevDate" + i).innerText = date[0] + ', ' + date[1] + ' ' + date[2]
            document.getElementById("weatherPrevIcon" + i).src = "https://openweathermap.org/img/wn/" + val[ICON] + ".png"
            document.getElementById("weatherPrevTemp" + i).innerText = `${Math.floor(val[HIGHEST])} / ${Math.floor(val[LOWEST])}°C`
            i++
        });

    }
}