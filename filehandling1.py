try:
    numerator=10
    denominator=int(input("enter a number"))
    result=numerator/denominator
    print(result)
except ZeroDivisionError:
    print("error :denominator cant be zero")
except IndexError:
    